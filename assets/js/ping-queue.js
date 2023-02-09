setInterval(function() {
	if(!pressedPlay) return;
	var xhr = new XMLHttpRequest();
	xhr.open("GET", "api/update-queue-timestamp.php", true);
	xhr.send();
}, 2000);
