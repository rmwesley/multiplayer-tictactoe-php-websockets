<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\EventLoop\Factory;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

class TicTacToe implements Ratchet\MessageComponentInterface {
	protected $queue;
	private $queueCounter;
	protected $connectionsMap;
	protected $usernamesMap;
	protected $db;

	public function __construct() {
		// A queue to hold WebSocket connections waiting for a match
		$this->queue = new \SplQueue;
		$queueCounter = 0;

		// A dictionary mapping WebSocket ids to their connections
		$this->connectionsMap = array();
		// A dictionary mapping WebSocket ids to their player usernames
		$this->usernamesMap = array();

		// Database connection
		$this->db = $GLOBALS['conn'];
	}

	public function onOpen(Ratchet\ConnectionInterface $from) {
		// Add new client connection to the dictionary
		$this->connectionsMap[$from->resourceId] = $from;
	}

	public function onClose(Ratchet\ConnectionInterface $from) {
		$ws_id = $from->resourceId;

		$this->dequeue($from);

		// Remove client connection from the dictionaries
		unset($this->connectionsMap[$ws_id]);
		unset($this->usernamesMap[$ws_id]);
	}

	private function enqueue($username, $from) {
		// Add client connection to the queue
		$this->queue->enqueue($from);
		$this->queueCounter++;

		$ws_id = $from->resourceId;
		// Keeping track of username
		$this->usernamesMap[$ws_id] = $username;

		$sql = "INSERT INTO match_queue (username, websocket_id) VALUES ('$username', '$ws_id')";
		$this->db->query($sql);

		// Call matchup if there are at least 2 players in the queue
		if ($this->queueCounter >= 2) {
			$this->matchup();
		}
	}

	private function dequeue($from) {
		$ws_id = $from->resourceId;
		$sql = "DELETE FROM match_queue WHERE websocket_id = '$ws_id'";
		$this->db->query($sql);
	}

	public function nextPlayer() {
		// We keep dequeueing until an active player is found
		while ($this->queueCounter > 0) {
			$client = $this->queue->dequeue();
			$this->queueCounter--;

			if(!isset($this->connectionsMap[$client->resourceId])) continue;
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
		$query = "INSERT INTO rooms (player1, player2) VALUES ('$player1->resourceId', '$player2->resourceId')";
		$result = $this->db->query($query);

		if (!$result) {
			// Failed to create the room, notify the players and put them back in the queue
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
		// Send a match found message to the players
		// This will ask them for confirmation to join a game room
		$response = json_encode(array(
			'type' => 'match_found',
			'room_id' => $room_id,
			'player1' => $this->usernamesMap[$player1->resourceId],
			'player2' => $this->usernamesMap[$player2->resourceId],
		));
		$player1->send($response);
		$player2->send($response);
	}


	public function onMessage(Ratchet\ConnectionInterface $from, $msg) {
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

			// Code for handling other message types
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
			$client = $this->connectionsMap[$ws_id];

			// Dequeueing client
			$this->dequeue($client);

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
