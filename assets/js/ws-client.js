const inactivityModal = $('#inactive-modal');
const joinModal = $('#join-modal');
var room_id = null;

function clientWebSocketInit() {
	//window.ws = new WebSocket("wss://localhost:8080", null, null, null, {
	//window.ws = new WebSocket("wss://127.0.0.1:8080", null, null, null, {
		//protocolVersion: 8,
		//origin: 'https://localhost:8080',
		//rejectUnauthorized: false
	//window.ws = new WebSocket("wss://0.0.0.0:8080");
	//window.ws = new WebSocket("wss://192.168.0.110:8080");
	window.ws = new WebSocket("wss://127.0.0.1:8080");

	window.ws.onopen = function () {
		// Send message to server to add user to the matchmaking queue
		usernamePromise.then((username) => {
			this.send(JSON.stringify({ type: "enqueue", username: username }));
		});
	};

	window.ws.onerror = function () {
		console.log("WebSocket error.");
	};
	window.ws.onmessage = (event) => {
		message = JSON.parse(event.data);
		console.log(message);
		// User was inactive and was removed from match queue
		if(message.type === 'inactive') {
			// Showing inactivity popup message
			inactivityModal.modal('show');
			hideWaitingRoom();
		}
		// Match found
		else if(message.type === 'match_found') {
			joinModal.find('#player1').text(message.player1);
			joinModal.find('#player2').text(message.player2);
			joinModal.find('.modal-title').append(message.room_id);
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
			window.location.href = "game.php?room_id=" + room_id;
		}
	}
}
