<?php
require_once 'Room.class.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\EventLoop\Factory;

require_once 'vendor/autoload.php';
require_once 'config/db.php';

class GameWsServer implements Ratchet\MessageComponentInterface {
	private $queue;
	private $queueCounter;
	private $rooms;
	protected $db;

	public function __construct() {
		// A queue to hold WebSocket connections waiting for a match
		$this->queue = new \SplQueue;
		$queueCounter = 0;

		// Currently open game rooms
		$this->rooms = array();

		// Database connection
		$this->db = $GLOBALS['db_conn'];
	}

	public function onOpen(Ratchet\ConnectionInterface $from) {
		$from->timestamp = time();
		$from->state = "opened";

		return;
	}

	public function onClose(Ratchet\ConnectionInterface $from) {
		if ($from->state == "joined_room") {
			$room_id = $from->roomId;
			$query = "DELETE FROM rooms WHERE id='$room_id'";
			$this->db->query($query);

			$opponent = $this->getOpponent($from);
			// If opponent already closed, just unset the room
			if($opponent->state == "closed"){
				unset($this->rooms[$room_id]);
			}
			else{
				$from->state = "closed";

				// Notify the player that their opponent has disconnected
				$response = json_encode([
					'type' => 'opponent_disconnected'
				]);
				$opponent->send($response);
				$opponent->close();
			}
		}
		$from->state = "closed";
	}

	private function enqueue($from) {
		// Add client connection to the queue
		$this->queue->enqueue($from);
		$this->queueCounter++;
		$from->state = "waiting";

		// Call matchup if there are at least 2 players in the queue
		if ($this->queueCounter >= 2) {
			$this->matchup();
		}
	}

	public function cleanInactive($client) {
		if($client == null){
			return;
		}

		// Closing connection due to inactivity
		// 4001 (Inactivity Timeout)
		$client->close(4001);
	}

	public function nextPlayer() {
		// We keep dequeueing until an active player is found
		while ($this->queueCounter > 0) {
			$client = $this->queue->dequeue();
			$this->queueCounter--;

			if(time() - $client->timestamp > 120){
				$this->cleanInactive($client);
				return;
			}
			if($client->state == "closed") continue;
			return $client;
		}
		return;
	}

	public function matchup(){
		// Get 2 next open connections as players
		$player1 = $this->nextPlayer();
		$player2 = $this->nextPlayer();

		// Could not find 2 available players
		if(!isset($player1) || !isset($player2)) return;

		// Same player in different sessions
		if($player1->username == $player2->username &&
			$player1->guestUser == $player2->guestUser) return;

		// Create a new entry in the rooms table
		$query = "INSERT INTO rooms (player1, player2) VALUES ('$player1->username', '$player2->username')";
		$result = $this->db->query($query);

		if (!$result) {
			// Failed to create the game room
			// Close the player's connections while notifying the error
			// 1011 (Internal Error)
			$player1->close(1011, "Database Error: Failed to create game room");
			$player2->close(1011, "Database Error: Failed to create game room");
			return;
		}

		$room_id = $this->db->insert_id;
		$player1->state = "joined_room";
		$player2->state = "joined_room";
		$player1->roomId = $room_id;
		$player2->roomId = $room_id;

		// Setting up new game room...
		$room = new Room($player1, $player2, $room_id);

		// Adding new room to rooms array
		$this->rooms[$room_id] = $room;

		// Send a match found message to the players
		// This will ask them for confirmation to join a game room
		$response = json_encode([
			'type' => 'match_found',
			'room_id' => $room_id,
			'player1' => $player1->username,
			'player2' => $player2->username,
		]);

		$player1->send($response);
		$player2->send($response);
	}

	public function getOpponent(Ratchet\ConnectionInterface $from){
		if(!isset($from->roomId)){
			return;
		}
		$room = $this->rooms[$from->roomId];
		if($room->getPlayer1() == $from){
			return $room->getPlayer2();
		}
		return $room->getPlayer1();
	}

	public function isAdmin($from){
		if($from->username == "alice") return true;
	}

	public function onMessage(Ratchet\ConnectionInterface $from, $msg) {
		$payload = json_decode($msg, true);
		$type = $payload['type'];

		switch($type) {
		case 'enqueue':
			$from->username = $payload['username'];
			$from->guestUser = $payload['guestUser'];
			$this->enqueue($from);
			break;
		case 'ping':
			$from->timestamp = time();
			break;
		case 'confirm':
			// Get opponent connection
			$opponent = $this->getOpponent($from);

			if(empty($opponent)){
				// Cannot confirm without an opponent set
				// 4003 (Invalid WS Message)
				$from->close(4003);
				return;
			}

			$from->state = "confirmed";

			$response = json_encode([
				'type' => 'opponent_confirmed',
			]);
			$opponent->send($response);

			if($opponent->state !== "confirmed") break;

			// Both players have confirmed, move them to the game room
			$response = json_encode([
				'type' => 'game_start',
			]);
			$from->send($response);
			$opponent->send($response);
			break;
		case 'get_room_data':
			$room_id = $payload['room_id'];
			$username = $payload['username'];

			$from->roomId = $room_id;
			$from->username = $username;

			$room = $this->rooms[$room_id] ?? null;
			if(empty($room)){
				// Forbidden room for current user
				// 4002 (Forbidden Room)
				$from->close(4002);
				return;
			}
			if(!$room->isInRoom($from) && !$this->isAdmin($from)){
				$from->send(
					json_encode("Access denied! You aren't in this room.")
				);
				break;
			}
			if($room->isInRoom($from)){
				$room->updatePlayerConnection($from);
			}

			$response = [
				"username1" => $room->getPlayer1()->username,
				"username2" => $room->getPlayer2()->username,
				"boardMarkings" => $room->getBoard(),
				"turn" => $room->getTurn(),
				"xPlayerNumber" => $room->getXPlayerNumber(),
				"type" => "room_data",
			];
			$from->send(json_encode($response));
			break;

		case "move":
			$room_id = $from->roomId;
			$room = $this->rooms[$room_id] ?? null;
			$move = $payload["tile"];

			if($room == null || $room->gameOver()){
				$from->send(json_encode([
					"type" => "game_over",
				]));
				return;
			}
			if(!$room->checkAvailability($move)){
				$from->send(json_encode([
					"type" => "invalid_move",
					"move" => $move,
				]));
				break;
			}
			$curr_player = $room->getCurrentPlayer();

			if($curr_player != $from) {
				$from->send(json_encode([
					"type" => "not_your_turn",
					"move" => $move,
				]));
				break;
			}
			$room->nextMove($move);
			$updated_board = $room->getBoard();
			$winner = $room->getWinner();

			if($winner != "_" && $winner != null){
				$message = [
					'type' => 'game_end',
					'lastMove' => $move,
					'moveSymbol' => $winner,
					'turn' => $room->getTurn(),
					'boardMarkings' => $updated_board,
					'winner' => $curr_player->username,
				];
				$sql = "UPDATE rooms SET winner = '$curr_player->username', board_markings = '$updated_board' WHERE id = '$room_id'";
				$this->db->query($sql);
				$room->getPlayer1()->send(json_encode($message));
				$room->getPlayer2()->send(json_encode($message));
				break;
			}
			$sql = "UPDATE rooms SET board_markings = '$updated_board' WHERE id = '$room_id'";
			$this->db->query($sql);

			if($room->getTurn() == 10){
				$message = [
					'type' => 'game_end',
					'lastMove' => $move,
					'moveSymbol' => $room->getCurrentSymbol(),
					'boardMarkings' => $updated_board,
				];
				$room->getPlayer1()->send(json_encode($message));
				$room->getPlayer2()->send(json_encode($message));
				break;
			}
			$message = [
				'type' => 'room_update',
				'lastMove' => $move,
				'moveSymbol' => $room->getCurrentSymbol(),
				'turn' => $room->getTurn(),
				'boardMarkings' => $updated_board,
			];
			$room->getPlayer1()->send(json_encode($message));
			$room->getPlayer2()->send(json_encode($message));
		}
	}

	public function onError(Ratchet\ConnectionInterface $from, Exception $e) {
		// Handle errors
	}
}

$ws_handler = new \Ratchet\Http\HttpServer(
	new \Ratchet\WebSocket\WsServer(
		new GameWsServer()
	)
);

$loop = \React\EventLoop\Loop::get();

$secure_websockets = new \React\Socket\SocketServer('127.0.0.1:8080', $context=array(), $loop);
$secure_websockets = new \React\Socket\SecureServer($secure_websockets, $loop, [
	'local_cert' => 'public.pem',
	'local_pk' => 'private.pem',
	'allow_self_signed' => TRUE, // Allow self signed certs (should be false in production)
	'verify_peer' => FALSE
]);

$secure_websockets_server =
	new \Ratchet\Server\IoServer($ws_handler, $secure_websockets, $loop);
$secure_websockets_server->run();
