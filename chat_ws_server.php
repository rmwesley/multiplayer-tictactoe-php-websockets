<?php
require_once 'ChatBox.class.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\EventLoop\Factory;

require_once 'vendor/autoload.php';
require_once 'config/db.php';

class ChatServer implements Ratchet\MessageComponentInterface {
	protected $connections;
	protected $chat;

    public function __construct() {
        $this->connections = new \SplObjectStorage;
		$this->chat = new ChatBox();
    }

	public function onOpen(Ratchet\ConnectionInterface $from) {
        $this->connections->attach($from);
	}

	public function onClose(Ratchet\ConnectionInterface $from) {
        $this->connections->detach($from);
	}

	public function onError(Ratchet\ConnectionInterface $from, Exception $e){
	}

	public function isAdmin($from){
		if($from->username == "admin" || $from->username == "rmwesley") return true;
	}

	public function onMessage(Ratchet\ConnectionInterface $from, $msg) {
		$payload = json_decode($msg, true);
		$type = $payload['type'];

		$timestamp = time();

		switch($type) {
		case 'join':
			// A new client joined, so we store its username
			$from->username = $payload['username'];
			break;
		case 'message':
			$this->chat->addMessage($payload['content']);
			foreach($this->connections as $conn) {
				$conn->send(json_encode([
					"source" => $from->username,
					"content" => $payload['content'],
				]));
			}

			break;
		}
	}

}

$ws_handler = new \Ratchet\Http\HttpServer(
	new \Ratchet\WebSocket\WsServer(
		new ChatServer()
	)
);

$loop = \React\EventLoop\Loop::get();

$secure_websockets = new \React\Socket\SocketServer('127.0.0.1:8081', $context=array(), $loop);
$secure_websockets = new \React\Socket\SecureServer($secure_websockets, $loop, [
	'local_cert' => 'public.pem',
	'local_pk' => 'private.pem',
	'allow_self_signed' => TRUE, // Allow self signed certs (should be false in production)
	'verify_peer' => FALSE
]);

$secure_websockets_server =
	new \Ratchet\Server\IoServer($ws_handler, $secure_websockets, $loop);
$secure_websockets_server->run();
