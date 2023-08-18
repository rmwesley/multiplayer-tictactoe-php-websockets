const n = 3;

// Get the URL of the current page
const url = new URL(window.location.href);

// Extract the values of room_id, player1 and player2 from the URL
const room_id = url.searchParams.get("room_id");

// Global DOM elements
var board, messageBox;

// Global variables
var player1, player2, boardMarkings, playerNumber, turn, symbol, opponent;
var gameSocket;

window.onload = () => {
	startChatBox();
	board = document.getElementById("board");
	messageBox = document.getElementById("message-box");

	// Board input as event listeners
	board.addEventListener("click", clickMark);
	document.body.addEventListener("keyup", keyMark);

	if(!room_id){
		invalidRoom();
		return;
	}
	board.classList.remove("d-none");
}

gameSocket = new WebSocket("wss://localhost:8080");

gameSocket.onopen = function () {
	// Send message to recover match data
	userIdentityPromise.then((data) => {
		message = {
			type: "get_room_data",
			room_id: room_id,
			username: data.username,
			guestUser: data.guestUser
		};
		gameSocket.send(JSON.stringify(message));
	});
}
gameSocket.onerror = function () {
	console.log("WebSocket error.");
};
gameSocket.onmessage = (event) => {
	message = JSON.parse(event.data);
	if(message.type == "room_data"){
		player1 = message.player1;
		player2 = message.player2;
		boardMarkings = message.boardMarkings;
		turn = message.turn;

		setupBoard();

		// Determining player order
		setPlayerNumber();

		// Storing player symbol
		setPlayerSymbol();

		// Showing turn information
		updateTurnMessageBox();
	}
	if(message.type == "room_update"){
		boardMarkings = message.boardMarkings;
		tile = document.getElementById(message.lastMove);
		tile.classList.add("disabled", message.moveSymbol)

		turn = message.turn;
		updateTurnMessageBox();
	}
	if(message.type == "invalid_move" || message.type == "not_your_turn"){
		document.getElementById(message.move)
			.classList.remove("disabled", symbol);
	}
	if(message.type == "game_end"){
		boardMarkings = message.boardMarkings;
		turn = message.turn;
		setupBoard();

		tile = document.getElementById(message.lastMove);
		tile.classList.add("disabled", message.moveSymbol)

		showResult();
	}
	if(message.type == "finished_game"){
		setPlayerNumber();

		boardMarkings = message.boardMarkings;
		setupBoard();

		updateTurnMessageBox();
	}
}

gameSocket.onclose = (event) => {
	if(event.code == 4002){
		invalidRoom();
	}
};

function setupBoard(){
	for(let i = 0; i < boardMarkings.length; i++){
		let symbol = boardMarkings.charAt(i);
		if(symbol == "_") continue;
		document.getElementById(i).classList.add(symbol, "disabled");
	}
}

function updateTurnMessageBox(){
	if(playerNumber == null) return;
	if(turn % 2 == playerNumber % 2){
		messageBox.querySelector(".players-turn")
			.classList.remove("d-none");
		messageBox.querySelector(".opponents-turn")
			.classList.add("d-none");
	}
	else{
		messageBox.querySelector(".players-turn")
			.classList.add("d-none");
		messageBox.querySelector(".opponents-turn")
			.classList.remove("d-none");
	}
}

function showResult(){
	userIdentityPromise.then((data) => {
		username = data.username;
		if (username != player1 && username != player2){
			return;
		}
		if (message.winner == null){
			document.getElementById("tie-message-box")
				.classList.remove("d-none");
		}
		else if (message.winner == username){
			document.getElementById("win-message-box")
				.classList.remove("d-none");
		}
		else{
			document.getElementById("lose-message-box")
				.classList.remove("d-none");
		}
	});
}

function setPlayerNumber() {
	userIdentityPromise.then((data) => {
		messageBox.querySelector(".waiting")
			.classList.add("d-none");
		username = data.username;
		// Non-playing user
		if(!userInRoom(username)){
			playerNumber = null;
			return;
		}
		if(player1 == username){
			playerNumber = 1;
		}
		else {
			playerNumber = 2;
		}
	});
}

function setPlayerSymbol(){
	userIdentityPromise.then((data) => {
		username = data.username;
		if (player1 == username) {
			symbol = message.mark1;
			opponent = player2;
		}
		else {
			opponent = player1;
			symbol = message.mark2;
		}
	});
}

function clickMark(event){
	if(!event.target.classList.contains("tile")) return;
	if(event.target.classList.contains("disabled")) return;
	gameSocket.send(JSON.stringify({type: "move", tile: event.target.id}));
}

function keyMark(event){
	if(event.key < '1') return;
	if(event.key > '9') return;
	document.getElementById(event.key-1).click();
}

function userInRoom(user){
	return (player1 == user || player2 == user);
}

function invalidRoom(){
	messageBox.querySelector(".waiting")
		.classList.add("d-none");
	messageBox.querySelector(".invalid")
		.classList.remove("d-none");
}
