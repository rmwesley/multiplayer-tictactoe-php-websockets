<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\EventLoop\Factory;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

class TicTacToe implements Ratchet\MessageComponentInterface {
	private $queue;
	private $queueCounter;
	private $connectionsMap;
	private $rooms;
	protected $db;

	public function __construct() {
		// A queue to hold WebSocket connections waiting for a match
		$this->queue = new \SplQueue;
		$queueCounter = 0;

		// A dictionary mapping WebSocket ids to their connections
		$this->connectionsMap = array();

		// Currently open game rooms
		$this->rooms = array();

		// Database connection
		$this->db = $GLOBALS['conn'];
	}

	public function onOpen(Ratchet\ConnectionInterface $from) {
		$from->state = "waiting";
		// Add new client connection to the dictionary
		$this->connectionsMap[$from->resourceId] = $from;
	}

	public function onClose(Ratchet\ConnectionInterface $from) {
		if ($from->state == "joined_game") {
			$room_id = $from->roomId;
			$opponent = $this->getOpponent($room_id, $from);
			// If opponent already closed, just unset the room
			if($opponent->state == "closed"){
				unset($this->rooms[$room_id]);
			}
			else{
				$from->state = "closed";
				$room_id = $from->roomId;
				$sql = "DELETE FROM rooms WHERE id = '$room_id'";
				$this->db->query($sql);

				// Notify the player that their opponent has disconnected
				$response = json_encode(array(
					'type' => 'opponent_disconnected'
				));
				$opponent->send($response);
				$opponent->close();
			}
		}
		$from->state = "closed";

		$ws_id = $from->resourceId;

		$this->dequeue($from->resourceId);

		// Remove client connection from the dictionaries
		unset($this->connectionsMap[$ws_id]);
	}

	private function enqueue($username, $from) {
		// Add client connection to the queue
		$this->queue->enqueue($from);
		$this->queueCounter++;

		$ws_id = $from->resourceId;
		// Keeping track of username
		$from->username = $username;

		$sql = "INSERT INTO match_queue (username, websocket_id) VALUES ('$username', '$ws_id')";
		$this->db->query($sql);

		// Call matchup if there are at least 2 players in the queue
		if ($this->queueCounter >= 2) {
			$this->matchup();
		}
	}

	private function dequeue($ws_id) {
		$sql = "DELETE FROM match_queue WHERE websocket_id = '$ws_id'";
		$this->db->query($sql);
	}

	public function nextPlayer() {
		// We keep dequeueing until an active player is found
		while ($this->queueCounter > 0) {
			$client = $this->queue->dequeue();
			$this->queueCounter--;

			if($client->state == "closed") continue;
			return $client;
		}
		return;
	}

	public function matchup(){
		// Get 2 next open connections as players
		$player1 = $this->nextPlayer();
		$player2 = $this->nextPlayer();
		if(!isset($player1) || !isset($player2)) return;

		// Create a new entry in the rooms table
		$query = "INSERT INTO rooms (player1, player2) VALUES ('$player1->username', '$player2->username')";
		$result = $this->db->query($query);

		if (!$result) {
			// Failed to create the room
			// Notify the players and put them back in the queue
			$response = json_encode(array(
				'type' => 'error',
				'message' => 'Failed to create game room'
			));
			$player1->send($response);
			$player2->send($response);

			$this->enqueue($player1);
			$this->enqueue($player2);
			return;
		}

		$room_id = $this->db->insert_id;
		$player1->state = "joined_game";
		$player2->state = "joined_game";
		$player1->roomId = $room_id;
		$player2->roomId = $room_id;
		$username1 = $player1->username;
		$username2 = $player2->username;

		// Setting up new room...
		$newRoom = new stdClass();
		$newRoom->roomId = $room_id;
		$newRoom->turn = 1;
		$newRoom->player1 = $player1;
		$newRoom->player2 = $player2;
		$newRoom->username1 = $username1;
		$newRoom->username2 = $username2;
		$newRoom->boardMarkings = "_________";
		$newRoom->mark1 = array("X", "O")[array_rand(array("X", "O"))];
		$newRoom->mark2 = "X";
		if($newRoom->mark1 == "X") $newRoom->mark2 = "O";

		// Adding new room to rooms list
		$this->rooms[$room_id] = $newRoom;

		// Send a match found message to the players
		// This will ask them for confirmation to join a game room
		$response = json_encode(array(
			'type' => 'match_found',
			'room_id' => $room_id,
			'player1' => $player1->username,
			'player2' => $player2->username,
		));

		$player1->send($response);
		$player2->send($response);
	}

	public function getOpponent($room_id, $client){
		if ($this->rooms[$room_id]->player1 == $client) {
			return $this->rooms[$room_id]->player2;
		}
		return $this->rooms[$room_id]->player1;
	}
	public function onMessage(Ratchet\ConnectionInterface $from, $msg) {
		//print_r($msg);
		//echo "\n";
		$payload = json_decode($msg, true);
		$type = $payload['type'];

		switch($type) {
		case 'enqueue':
			$username = $payload['username'];
			$this->enqueue($username, $from);
			break;
		case 'ping':
			$ws_id = $from->resourceId;
			$sql = "SELECT * FROM match_queue WHERE websocket_id = '$ws_id'";
			$result = $this->db->query($sql);
			if ($result->num_rows > 0) {
				$sql = "UPDATE match_queue SET last_heartbeat_ts = NOW() WHERE websocket_id = '$ws_id'";
				$this->db->query($sql);
			} else {
				$response = json_encode(array(
					'type' => 'inactive',
				));
				$from->send($response);
			}
			break;

		case 'confirm':
			$from->state = "confirmed";

			// Get the room ID from the connection object
			$room_id = $from->roomId;

			// Get opponent connection
			$opponent = $this->getOpponent($room_id, $from);

			$response = json_encode(array(
				'type' => 'opponent_confirmed',
			));
			$opponent->send($response);

			if($opponent->state !== "confirmed") break;

			// Both players have confirmed, move them to the game room
			$response = json_encode(array(
				'type' => 'game_start',
			));
			$from->send($response);
			$opponent->send($response);
			break;
		}
	}

	public function queueCleaner() {
		$threshold = time() - 6;
		$query = "SELECT * FROM match_queue WHERE UNIX_TIMESTAMP(last_heartbeat_ts) < $threshold";
		$result = $this->db->query($query);

		// Looping entire TABLE
		while ($row = $result->fetch_assoc()) {
			// Getting the connection from WebSocket id
			$ws_id = $row['websocket_id'];
			$client = $this->connectionsMap[$ws_id] ?? null;

			// Dequeueing client
			$this->dequeue($ws_id);

			if($client == null){
				continue;
			}

			// Sending message to client so they know why they were removed
			$response = json_encode(array(
				'type' => 'inactive',
			));
			$client->send($response);

			// Closing connection since client is not in queue anymore
			$client->close();
		}
	}

	public function onError(Ratchet\ConnectionInterface $from, Exception $e) {
		// Handle errors
	}
}

$loop = Factory::create();
$tictactoe = new TicTacToe();

$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			$tictactoe
		)
	),
	8080
);

$server->loop->addPeriodicTimer(3, function () use ($tictactoe) {
	$tictactoe->queueCleaner();
});

$server->run();
