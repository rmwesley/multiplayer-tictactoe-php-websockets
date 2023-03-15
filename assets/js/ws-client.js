const inactivityModal = $('#inactive-modal');
const joinModal = $('#join-modal');

function clientWebSocketInit() {
	window.ws = new WebSocket("ws://localhost:8080");

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
			joinModal.modal('show');
		}
	}
}
