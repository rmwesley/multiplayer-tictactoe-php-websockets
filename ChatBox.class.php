<?php
const MESSAGE_COUNT=500;

class ChatBox {
	private $messages;
	private int $end;

	public function __construct() {
		$this->messages = new SplFixedArray(MESSAGE_COUNT);

		// Circular list of messages
		for ($i = 0; $i < MESSAGE_COUNT; $i++) {
			$message[$i] = null;
		}
		$this->end = 0;
	}
	public function addMessage($message){
		// Circular list, so we take the modulo
		$this->end = $this->end % MESSAGE_COUNT;

		$this->messages[$this->end] = $message;
		$this->end++;
	}
	public function toJSON(){
		$behind = array_slice($this->messages, 0, $this->end);
		$ahead = array_slice($this->messages, $this->end);
		json_encode(array_merge($ahead, $behind));
	}
}
