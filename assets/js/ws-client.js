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
		message = event.data;
		if(message.type = 'inactive') showInactivityPopup();
	}
}
