const n = 3;

// Get the URL of the current page
const url = new URL(window.location.href);

// Extract the values of room_id, player1 and player2 from the URL
const room_id = url.searchParams.get("room_id");
window.onload = () => {
	startChatBox();
	const board = document.getElementById("board");
	// Listen for click events on board
	board.addEventListener("click", mark)
	// Add styling to board tiles
/*
	board.querySelectorAll('.tile').forEach((element) => {
		//element.classList.add("col-lg-2", "col-md-2", "col-sm-2", "btn", "btn-primary", "p-5");
		//element.classList.add("col-md-2", "btn", "btn-primary", "p-5");
		//element.classList.add("col-md-2", "btn", "btn-primary", "w-25", "p-1");
	});
*/
}

window.socket = new WebSocket("wss://localhost:8080");
window.socket.onopen = function () {
	// Send message to recover match data
	userIdentityPromise.then((username) => {
		message = {
			type: "get_room_data",
			room_id: room_id,
			username: username
		};
		window.socket.send(JSON.stringify(message));
	});
}
window.socket.onerror = function () {
	console.log("WebSocket error.");
};
window.socket.onmessage = (event) => {
	// Player can *try* making a move again...
	window.alreadyMadeMove = false;
	// Hide loading icons
	board.querySelectorAll(".loading").forEach((element) => {
		element.classList.add("d-none");
	});
	message = JSON.parse(event.data);
	if(message.type == "room_data"){
		window.boardMarkings = message.boardMarkings;
		for(let tileIndex = 0; tileIndex < window.boardMarkings.length; tileIndex++){
			let symbol = window.boardMarkings.charAt(tileIndex);
			if(symbol == "_") continue;
			//tileElement = document.getElementById(tileIndex);
			saveMove(tileIndex, symbol);
		}
		window.turn = message.turn;
		// Storing current player symbol and opponent username
		userIdentityPromise.then((username) => {
			// Current player is player1
			if (message.username1 == username) {
				window.opponent = message.username2;
				if(message.xPlayerNumber == 1){
					window.symbol = "X";
					return;
				}
				window.symbol = "O";
				return;
			}
			// Current player is player2
			window.opponent = message.username1;
			if(message.xPlayerNumber == 2){
				window.symbol = "X";
				return;
			}
			window.symbol = "O";
			return;
		});
	}
	if(message.type == "room_update"){
		window.boardMarkings = message.boardMarkings;
		window.turn = message.turn;

		//tileElement = document.getElementById(message.lastMove);
		saveMove(message.lastMove, message.moveSymbol)
	}
	if(message.type == "invalid_move"){
		document.getElementById(message.move)
			.classList.remove(window.symbol);
	}
	if(message.type == "not_your_turn"){
		document.getElementById(message.move)
			.classList.remove("disabled", window.symbol);
	}
	if(message.type == "game_end"){
		window.boardMarkings = message.boardMarkings;
		window.turn = message.turn;

		saveMove(message.lastMove, message.moveSymbol);

		userIdentityPromise.then((username) => {
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

function saveMove(tileIndex, symbol){
	tileElement = document.getElementById(tileIndex);
	tileElement.classList.add("disabled");
	symbolElement = tileElement.querySelector("." + symbol);
	symbolElement.classList.remove("d-none");
}
function loadingMove(tileIndex){
	tileElement = document.getElementById(tileIndex);
	tileElement.classList.add("disabled");
	loadingIcon = tileElement.querySelector(".loading");
	loadingIcon.classList.remove("d-none");
}

function mark(event){
	if(!event.target.classList.contains("tile")) return;
	if(event.target.classList.contains("disabled")) return;
	if(window.alreadyMadeMove) return;

	window.alreadyMadeMove = true;
	window.socket.send(JSON.stringify(
		{type: "move", tile: event.target.id}
	));
	loadingMove(event.target.id);
}
