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
	protected $usernamesMap;
	protected $db;

	public function __construct() {
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

		// Remove player corresponding to closed websocket from queue
		$this->dequeue($ws_id);

		// Remove client connection from the dictionaries
		unset($this->connectionsMap[$ws_id]);
		unset($this->usernamesMap[$ws_id]);
	}

	private function enqueue($ws_id) {
		// Keeping track of username
		$this->usernamesMap[$ws_id] = $username;

		$sql = "INSERT INTO match_queue (username, websocket_id) VALUES ('$username', '$ws_id')";
		$this->db->query($sql);
	}

	private function dequeue($ws_id) {
		$sql = "DELETE FROM match_queue WHERE websocket_id = '$ws_id'";
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
		case 'ping':
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

		while ($row = $result->fetch_assoc()) {
			$ws_id = $row['websocket_id'];
			$this->dequeue($ws_id);

			// Sending 'inactive' message to client
			$response = json_encode(array(
				'type' => 'inactive',
			));
			$this->connectionsMap[$ws_id]->send($response);

			// Closing connection manually
			$this->connectionsMap[$ws_id]->close();
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
