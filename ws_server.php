<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\EventLoop\Factory;
use Ratchet\RFC6455\Messaging\Frame;

require_once '../vendor/autoload.php';
require_once 'config/db.php';

class Room {
	private $roomId;
	private $board = "_________";
	private $turn = 1;
	private $player1;
	private $player2;
	private $xPlayerNumber;

	function linkPlayers(){
		$this->player1->roomId = $this->roomId;
		$this->player2->roomId = $this->roomId;
	}
	function __construct($player1, $player2, $roomId){
		$this->player1 = $player1;
		$this->player2 = $player2;
		$this->roomId = $roomId;
		$this->xPlayerNumber = rand(1,2);

		$this->linkPlayers();
	}

	function getPlayer1(){
		return $this->player1;
	}
	function getPlayer2(){
		return $this->player2;
	}
	function getXPlayerNumber(){
		return $this->xPlayerNumber;
	}
	function getTurn(){
		return $this->turn;
	}
	function getBoard(){
		return $this->board;
	}
	function updatePlayerConnection($from){
		if ($from->username == $this->player1->username) {
			$this->player1 = $from;
		}
		else $this->player2 = $from;
	}
	function isInRoom($from){
		if($from->username == $this->player1->username
			|| $from->username == $this->player2->username){
				return true;
			}
		return false;
	}
	function getCurrentPlayer(){
		// Odd turn
		if($this->getTurn()%2 == 1){
			return $this->player1;
		}
		return $this->player2;
	}
	function currentIsX(){
		// Turn is odd
		if($this->getTurn()%2 == 1){
			// Is odd player X?
			return $this->xPlayerNumber == 1;
		}
		// Turn is even
		// Is even player X?
		return $this->xPlayerNumber == 2;
	}
	function getCurrentSymbol(){
		if ($this->currentIsX()){
			return 'X';
		}
		return 'O';
	}
	function nextMove($move){
		$this->turn++;
		$this->board[$move] = $this->getCurrentSymbol();
	}

	function getWinner(){
		if($this->getTurn() < 3) return;
		$winner = $this->checkVerticals();
		$winner = $this->checkHorizontals() ?? $winner;
		$winner = $this->checkDiagonals() ?? $winner;
		return $winner;
	}
	function gameOver(){
		if($this->turn > 9) return true;
		if($this->getWinner() != null) return true;
	}
	function checkHorizontals(){
		for ($i=0; $i<3; $i++) {
			$winner = $this->board[3*$i];
			if($winner == "_") continue;
			if($this->board[3*$i+1] == $winner && $this->board[3*$i+2] == $winner){
				return $winner;
			}
		}
	}
	function checkVerticals(){
		for ($j=0; $j<3; $j++) {
			$winner = $this->board[$j];
			if($winner == "_") continue;
			if($this->board[$j+3] == $winner && $this->board[$j+6] == $winner){
				return $winner;
			}
		}
	}
	function checkDiagonals(){
		$winner = $this->board[4];
		if($winner == "_") return;
		if($this->board[0] == $winner && $this->board[8] == $winner) return $winner;
		if($this->board[2] == $winner && $this->board[6] == $winner) return $winner;
	}

	function checkAvailability($move){
		return $this->board[$move] == "_";
	}
}

class WsHandler implements Ratchet\MessageComponentInterface {
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
		// Add new client connection to the dictionary
		$this->connectionsMap[$from->resourceId] = $from;
		$from->timestamp = time();
		$from->state = "opened";

		return;
	}

	public function onClose(Ratchet\ConnectionInterface $from) {
		if ($from->state == "joined_room") {
			$room_id = $from->roomId;
			$opponent = $this->getOpponent($from);
			// If opponent already closed, just unset the room
			if($opponent->state == "closed"){
				unset($this->rooms[$room_id]);
			}
			else{
				$from->state = "closed";

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
	public function cleanInactive($client) {
		if($client == null){
			return;
		}

		// Closing connection due to inactivity
		// 4001 (Inactivity Timeout)
		$client->close(new Frame(pack('n', "Inactivity Timeout"), true, 4001));
	}

	public function nextPlayer() {
		// We keep dequeueing until an active player is found
		while ($this->queueCounter > 0) {
			$client = $this->queue->dequeue();
			$this->queueCounter--;

			if(time() - $client->timestamp > 10){
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
		$response = json_encode(array(
			'type' => 'match_found',
			'room_id' => $room_id,
			'player1' => $player1->username,
			'player2' => $player2->username,
		));

		$player1->send($response);
		$player2->send($response);
	}

	public function getOpponent(Ratchet\ConnectionInterface $from){
		// Get the room from its ID stored in the connection object
		//$player1 = $this->rooms[$from->roomId]->player1;
		//if ($from == $player1) {
		//    return $this->rooms[$from->roomId]->player2;
		//}
		//return $player1;
		if(!isset($from->roomId)){
			echo "Player ".$from->username." is not yet in a room\n";
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
			$from->timestamp = time();
			break;
		case 'confirm':
			$from->state = "confirmed";

			// Get opponent connection
			$opponent = $this->getOpponent($from);

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
		case 'get_room_data':
			$room_id = $payload['room_id'];
			$username = $payload['username'];

			$from->roomId = $room_id;
			$from->username = $username;

			$room = $this->rooms[$room_id] ?? null;
			if($room==null){
				// Forbidden room for current user
				// 4002 (Forbidden Room)
				$from->close(new Frame(pack('n', "Forbidden Room"), true, 4002));
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

			$response = array(
				"username1" => $room->getPlayer1()->username,
				"username2" => $room->getPlayer2()->username,
				"boardMarkings" => $room->getBoard(),
				"turn" => $room->getTurn(),
				"xPlayerNumber" => $room->getXPlayerNumber(),
				"type" => "room_data",
			);
			$from->send(json_encode($response));
			break;

		case "move":
			$room_id = $from->roomId;
			$room = $this->rooms[$room_id];
			$move = $payload["tile"];

			if($room->gameOver()){
				$from->send(json_encode(array(
					"type" => "game_over",
				)));
				return;
			}
			if(!$room->checkAvailability($move)){
				$from->send(json_encode(array(
					"type" => "invalid_move",
					"move" => $move,
				)));
				break;
			}
			$curr_player = $room->getCurrentPlayer();

			if($curr_player != $from) {
				$from->send(json_encode(array(
					"type" => "not_your_turn",
					"move" => $move,
				)));
				break;
			}
			$room->nextMove($move);
			$updated_board = $room->getBoard();
			$winner = $room->getWinner();

			if($winner != "_" && $winner != null){
				$message = array(
					'type' => 'game_end',
					'lastMove' => $move,
					'moveSymbol' => $winner,
					'turn' => $room->getTurn(),
					'board' => $updated_board,
					'winner' => $curr_player->username,
				);
				$sql = "UPDATE rooms SET winner = '$curr_player->username', board_markings = '$updated_board' WHERE id = '$room_id'";
				$this->db->query($sql);
				$room->getPlayer1()->send(json_encode($message));
				$room->getPlayer2()->send(json_encode($message));
				break;
			}
			$sql = "UPDATE rooms SET board_markings = '$updated_board' WHERE id = '$room_id'";
			$this->db->query($sql);

			if($room->getTurn() == 10){
				$message = array(
					'type' => 'game_end',
					'lastMove' => $move,
					'moveSymbol' => $room->getCurrentSymbol(),
					'board' => $updated_board,
				);
				$room->getPlayer1()->send(json_encode($message));
				$room->getPlayer2()->send(json_encode($message));
				break;
			}
			$message = array(
				'type' => 'room_update',
				'lastMove' => $move,
				'moveSymbol' => $room->getCurrentSymbol(),
				'turn' => $room->getTurn(),
				'board' => $updated_board,
			);
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
        new WsHandler()
    )
);

$loop = \React\EventLoop\Loop::get();

$secure_websockets = new \React\Socket\SocketServer('127.0.0.1:8080', $context=array(), $loop);
$secure_websockets = new \React\Socket\SecureServer($secure_websockets, $loop, [
    'local_cert' => 'public.pem',
	'local_pk' => 'private.pem',
	'allow_self_signed' => TRUE,
    'verify_peer' => FALSE
]);

$secure_websockets_server =
	new \Ratchet\Server\IoServer($ws_handler, $secure_websockets, $loop);
$secure_websockets_server->run();
