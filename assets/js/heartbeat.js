function sendPing() {
	// Don't send ping if tab is not active or Play has not been pressed
	if(!pressedPlay || document.visibilityState !== "visible") return; 

	console.log("Pinging...");
	usernamePromise.then((username) => {
		ws.send(JSON.stringify({ type: "ping", username: username }));
	});
}

setInterval(() => {
	if(!pressedPlay) return;
	sendPing();
}, 3000);

document.addEventListener("visibilitychange", () => {
	sendPing();
});
