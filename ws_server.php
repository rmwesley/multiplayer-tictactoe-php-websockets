<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\EventLoop\Factory;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

class TicTacToe implements Ratchet\MessageComponentInterface {
	protected $connectionsMap;
	protected $db;

	public function __construct() {
		// A dictionary mapping websocket ids to their connections
		$this->connectionsMap = array();
		// Database connection
		$this->db = $GLOBALS['conn'];
	}

	public function onOpen(Ratchet\ConnectionInterface $from) {
		// Add new client connection to the dictionary
		$this->connectionsMap[$from->resourceId] = $from;
	}

	public function onClose(Ratchet\ConnectionInterface $from) {
		$ws_id = $from->resourceId;

		// Remove player corresponding to closed websocket from queue
		$this->dequeue($ws_id);

		// Remove client connection from the dictionary
		unset($this->connectionsMap[$ws_id]);
	}

	private function enqueue($username, $ws_id) {
		$sql = "INSERT INTO match_queue (username, websocket_id) VALUES ('$username', '$ws_id')";
		$this->db->query($sql);
	}

	private function dequeue($ws_id) {
		$sql = "DELETE FROM match_queue WHERE websocket_id = '$ws_id'";
		$this->db->query($sql);
	}
	private function dequeueUser($username) {
		$sql = "DELETE FROM match_queue WHERE username = '$username'";
		$this->db->query($sql);
	}

	public function onMessage(Ratchet\ConnectionInterface $from, $msg) {
		$ws_id = $from->resourceId;

		$payload = json_decode($msg, true);
		$type = $payload['type'];

		switch($type) {
		case 'enqueue':
			$username = $payload['username'];
			$this->enqueue($username, $from->resourceId);
			break;
		case 'dequeue':
			$this->dequeue($from->resourceId);
			break;
		case 'ping':
			$sql = "UPDATE match_queue SET last_heartbeat_ts = NOW() WHERE websocket_id = '$ws_id'";
			$this->db->query($sql);
			break;

			// Code for handling other message types
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

$server->run();
