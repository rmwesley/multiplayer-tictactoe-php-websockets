const n = 3;

// Get the URL of the current page
const url = new URL(window.location.href);

// Extract the values of room_id, player1 and player2 from the URL
const room_id = url.searchParams.get("room_id");

window.onload = () => {
	startChatBox();
	window.board = document.getElementById("board");
	window.messageBox = document.getElementById("message-box");
	board.addEventListener("click", mark)
	if(room_id === undefined){
		invalidRoom();
	}
}

window.socket = new WebSocket("wss://localhost:8080");
window.socket.onopen = function () {
	// Send message to recover match data
	userIdentityPromise.then((data) => {
		message = {
			type: "get_room_data",
			room_id: room_id,
			username: data.username,
			guestUser: data.guestUser
		};
		window.socket.send(JSON.stringify(message));
	});
}
window.socket.onerror = function () {
	console.log("WebSocket error.");
};
window.socket.onmessage = (event) => {
	message = JSON.parse(event.data);
	if(message.type == "room_data"){
		console.log(message);
		window.username1 = message.username1;
		window.username2 = message.username2;
		window.boardMarkings = message.boardMarkings;

		window.playerNumber = userIdentityPromise.then((data) => {
			username = data.username;
			if(window.username1 == username){
				return 1;
			}
			return 2;
		});
		window.playerNumber.then(()=>{
			window.messageBox.querySelector(".waiting")
				.classList.add("d-none");
		})
		updateMessageBox();

		for(let i = 0; i < window.boardMarkings.length; i++){
			let symbol = window.boardMarkings.charAt(i);
			if(symbol == "_") continue;
			document.getElementById(i).classList.add(symbol, "disabled");
		}
		window.turn = message.turn;

		// Storing player symbol
		userIdentityPromise.then((data) => {
			username = data.username;
			if (window.username1 == username) {
				window.symbol = message.mark1;
				window.opponent = window.username2;
			}
			else {
				window.opponent = username1;
				window.symbol = message.mark2;
			}
		});
	}
	if(message.type == "room_update"){
		console.log(message);
		updateMessageBox();

		window.boardMarkings = message.boardMarkings;
		window.turn = message.turn;

		tile = document.getElementById(message.lastMove);
		tile.classList.add("disabled", message.moveSymbol)
		console.log(tile);
	}
	if(message.type == "invalid_move"){
		console.log(message);
		document.getElementById(message.move)
			.classList.remove(window.symbol);
	}
	if(message.type == "opponent_turn"){
		console.log(message);
		document.getElementById(message.move)
			.classList.remove("disabled", window.symbol);
	}
	if(message.type == "game_end"){
		console.log(message);
		window.boardMarkings = message.boardMarkings;
		window.turn = message.turn;

		tile = document.getElementById(message.lastMove);
		tile.classList.add("disabled", message.moveSymbol)

		window.board.querySelectorAll('tile').forEach((tile) => {
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

window.socket.onclose = (event) => {
	console.log('WebSocket closed with code:', event.code);
	console.log('Close reason:', event.reason);

	if(event.code == 4002){
		console.log("Forbiden Room")
		invalidRoom();
	}
};

function updateMessageBox(){
	window.playerNumber.then((playerNumber) => {
		if(window.turn % 2 == playerNumber % 2){
			window.messageBox.querySelector(".players-turn")
				.classList.remove("d-none");
			window.messageBox.querySelector(".opponents-turn")
				.classList.add("d-none");
		}
		else{
			window.messageBox.querySelector(".players-turn")
				.classList.add("d-none");
			window.messageBox.querySelector(".opponents-turn")
				.classList.remove("d-none");
		}
	});
}

function mark(event){
	if(!event.target.classList.contains("tile")) return;
	if(event.target.classList.contains("disabled")) return;
	window.socket.send(JSON.stringify({type: "move", tile: event.target.id}));
}

function invalidRoom(){
	window.messageBox.querySelector(".waiting")
		.classList.add("d-none");
	window.messageBox.querySelector(".invalid")
		.classList.remove("d-none");
	window.board.classList.add("d-none");
}
