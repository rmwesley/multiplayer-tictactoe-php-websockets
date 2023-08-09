<?php
const MESSAGE_COUNT=100;

class ChatBox {
	private $messages;
	private int $start;

	public function __construct() {
		$this->messages = new SplFixedArray(MESSAGE_COUNT);

		// Circular list of messages being initialized
		for ($i = 0; $i < MESSAGE_COUNT; $i++) {
			$message[$i] = null;
		}
		$this->start = 0;
	}
	public function addMessage($source, $content, $time){
		// Circular list, so we take the modulo
		$this->start = $this->start % MESSAGE_COUNT;

		$this->messages[$this->start] = [$source, $content, $time];
		// Notice that $this->start is never 0 after this step...
		$this->start++;
	}
	public function isFull(){
		return $this->start >= MESSAGE_COUNT;
	}
	public function sortedMessages(){
		if($this->isFull()) return $this->messages;
		$result = new SplFixedArray(MESSAGE_COUNT);

		// Oldest messages come after starting position
		for ($i = $this->start; $i < MESSAGE_COUNT; $i++) {
			$result[$i - $this->start] = $this->messages[$i];
		}
		// Oldest messages come before starting position
		for ($i = 0; $i < $this->start; $i++) {
			$result[$i + MESSAGE_COUNT - $this->start] = $this->messages[$i];
		}
		return $result;
	}
}
