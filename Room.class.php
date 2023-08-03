<?php
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
