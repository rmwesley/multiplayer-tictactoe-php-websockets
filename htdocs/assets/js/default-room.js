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
	board.addEventListener("click", mark)
	if(room_id === undefined){
		invalidRoom();
	}
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
		console.log(message);
		player1 = message.username1;
		player2 = message.username2;
		boardMarkings = message.boardMarkings;

		playerNumber = userIdentityPromise.then((data) => {
			username = data.username;
			if(player1 == username){
				return 1;
			}
			return 2;
		});
		playerNumber.then(()=>{
			messageBox.querySelector(".waiting")
				.classList.add("d-none");
		})
		updateMessageBox();

		for(let i = 0; i < boardMarkings.length; i++){
			let symbol = boardMarkings.charAt(i);
			if(symbol == "_") continue;
			document.getElementById(i).classList.add(symbol, "disabled");
		}
		turn = message.turn;

		// Storing player symbol
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
	if(message.type == "room_update"){
		console.log(message);
		updateMessageBox();

		boardMarkings = message.boardMarkings;
		turn = message.turn;

		tile = document.getElementById(message.lastMove);
		tile.classList.add("disabled", message.moveSymbol)
		console.log(tile);
	}
	if(message.type == "invalid_move"){
		console.log(message);
		document.getElementById(message.move)
			.classList.remove(symbol);
	}
	if(message.type == "opponent_turn"){
		console.log(message);
		document.getElementById(message.move)
			.classList.remove("disabled", symbol);
	}
	if(message.type == "game_end"){
		console.log(message);
		boardMarkings = message.boardMarkings;
		turn = message.turn;

		tile = document.getElementById(message.lastMove);
		tile.classList.add("disabled", message.moveSymbol)

		board.querySelectorAll('tile').forEach((tile) => {
			tile.classList.add("disabled");
		});

		userIdentityPromise.then((data) => {
			username = data.username;
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
}

gameSocket.onclose = (event) => {
	console.log('WebSocket closed with code:', event.code);
	console.log('Close reason:', event.reason);

	if(event.code == 4002){
		console.log("Forbiden Room")
		invalidRoom();
	}
};

function updateMessageBox(){
	playerNumber.then((playerNumber) => {
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
	});
}

function mark(event){
	if(!event.target.classList.contains("tile")) return;
	if(event.target.classList.contains("disabled")) return;
	gameSocket.send(JSON.stringify({type: "move", tile: event.target.id}));
}

function invalidRoom(){
	messageBox.querySelector(".waiting")
		.classList.add("d-none");
	messageBox.querySelector(".invalid")
		.classList.remove("d-none");
	board.classList.add("d-none");
}
