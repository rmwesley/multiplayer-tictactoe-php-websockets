function sendPing() {
	// Don't send ping if tab is not active or Play has not been pressed
	if(!pressedPlay || document.visibilityState !== "visible") return; 

	// Ping the server to keep connection alive
	usernamePromise.then((username) => {
		window.ws.send(JSON.stringify({ type: "ping", username: username }));
	});
}

setInterval(() => {
	if(!pressedPlay) return;
	sendPing();
}, 3000);

document.addEventListener("visibilitychange", () => {
	sendPing();
});
