const ws = new WebSocket("ws://localhost:8080");

ws.onopen = function () {
	console.log("WebSocket connection established.");
};

ws.onclose = function () {
	console.log("WebSocket connection closed.");
};

ws.onerror = function () {
	console.log("WebSocket error.");
};
