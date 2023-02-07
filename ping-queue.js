setInterval(function() {
	if(!pressedPlay) return;
	var xhr = new XMLHttpRequest();
	xhr.open("GET", "update-queue-timestamp.php", true);
	xhr.send();
}, 2000);
