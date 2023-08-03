const inactivityModal = $('#inactive-modal');
const joinModal = $('#join-modal');
var room_id = null;

function clientWebSocketInit() {
	window.ws = new WebSocket("wss://127.0.0.1:8080");

	window.ws.onopen = function () {
		// Send a message to server to enqueue client websocket
		usernamePromise.then((username) => {
			this.send(JSON.stringify({
				"type": "enqueue",
				"username": username
			}));
		})
	};

	window.ws.onclose = (event) => {
		// User was inactive and was removed from match queue
		if(event.code == 4001){
			// Showing inactivity popup message
			inactivityModal.modal('show');
			hideWaitingRoom();
			//console.log("Connection closed due to inactivity. The player didn't send a ping/heartbeat message on the expected time frame")
		}
		setTimeout(300, hideWaitingRoom);
	};

	window.ws.onmessage = (event) => {
		message = JSON.parse(event.data);
		// Match found
		if(message.type === 'match_found') {
			joinModal.find('#player1').text(message.player1);
			joinModal.find('#player2').text(message.player2);
			joinModal.find('.modal-title').text("Join Room " + message.room_id);
			room_id = message.room_id;

			joinModal.modal('show');
			usernamePromise.then((username) => {
				if(joinModal.find('#player1').text() == username) {
					window.player = joinModal.find('#player1');
					window.opponent = joinModal.find('#player2');
				}
				else{
					window.player = joinModal.find('#player2');
					window.opponent = joinModal.find('#player1');
				}
			});
		}
		else if(message.type === "opponent_confirmed") {
			// Update UI to show opponent confirmed
			window.opponent.parent().parent().find(".tick").text("✓");
		}
		else if(message.type === "opponent_disconnected"){
			// Update UI to show opponent disconnected
			window.opponent.parent().parent().find(".tick").text("✕");
			confirmBtn.prop("disabled", true);
			cancel();
		}
		else if(message.type === "game_start"){
			// Redirect to game room
			window.location.href = "index.php?page=game&room_id=" + room_id;
		}
	}
}
