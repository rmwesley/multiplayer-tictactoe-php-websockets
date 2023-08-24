// Global DOM elements
var playerElement, opponentElement;
// Global variables
var room_id, refreshIntervalId;
var waitingRoomWs;

function pingServer() {
	// Don't send ping if tab is not active or Play has not been pressed
	if(document.visibilityState !== "visible") return; 

	// Ping the server to keep connection alive
	waitingRoomWs.send(JSON.stringify({ type: "ping"}));
}
function clientWebSocketInit() {
	waitingRoomWs = new WebSocket("wss://127.0.0.1:8080");

	waitingRoomWs.onopen = function () {
		// Send a message to server to enqueue client websocket
		userIdentityPromise.then((data) => {
			this.send(JSON.stringify({
				"type": "enqueue",
				"username": data.username,
				"guestUser": data.guestUser
			}));
		})
		// Keep the id so that it can be closed when WS closes
		refreshIntervalId = setInterval(pingServer, 30000);

		// Send ping when visibility of page changes
		document.addEventListener("visibilitychange", () => {
			sendPing();
		});
	};

	waitingRoomWs.onclose = (event) => {
		clearInterval(refreshIntervalId);

		// User was inactive and was removed from match queue
		if(event.code == 4001){
			// Showing inactivity popup message
			inactivityModal = new bootstrap.Modal(inactivityModalElement);
			inactivityModal.show();

			hideWaitingRoom();
		}
		setTimeout(300, hideWaitingRoom);
	};

	waitingRoomWs.onmessage = (event) => {
		message = JSON.parse(event.data);
		// Match found
		if(message.type === 'match_found') {
			joinModal = new bootstrap.Modal(joinModalElement);
			joinModal.show();

			joinModalElement.querySelector('#player1').innerHTML = message.player1;
			joinModalElement.querySelector('#player2').innerHTML = message.player2;
			joinModalElement.querySelector('.modal-title').innerHTML = "Join Room " + message.room_id;
			room_id = message.room_id;

			userIdentityPromise.then((data) => {
				username = data.username;
				if(joinModalElement.querySelector('#player1').innerHTML == username) {
					playerElement = joinModalElement.querySelector('#player1');
					opponentElement = joinModalElement.querySelector('#player2');
				}
				else{
					playerElement = joinModalElement.querySelector('#player2');
					opponentElement = joinModalElement.querySelector('#player1');
				}
			});
		}
		else if(message.type === "opponent_confirmed") {
			// Update UI to show opponent confirmed
			opponentElement.parentNode.parentNode.querySelector(".tick").innerHTML = "✓";
		}
		else if(message.type === "opponent_disconnected"){
			// Update UI to show opponent disconnected
			opponentElement.parentNode.parentNode.querySelector(".tick").innerHTML = "✕";
			confirmBtn.classList.replace("disabled", true);
			cancel();
		}
		else if(message.type === "game_start"){
			// Redirect to game room
			window.location.href = "index.php?page=game&room_id=" + room_id;
		}
	}
}

function confirmMatch(){
	playerElement.parentNode.parentNode.querySelector(".tick").innerHTML = "✓";
	// Disable button
	confirmBtn.classList.replace("disabled", true);

	// Send confirmation message to server
	var message = {
		type: "confirm",
	};
	waitingRoomWs.send(JSON.stringify(message));
}
