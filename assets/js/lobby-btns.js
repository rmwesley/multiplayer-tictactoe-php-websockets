const playBtn = document.querySelector("#playBtn");
const cancelBtn = document.querySelector("#cancelBtn");
const waitingRoom = document.querySelector("#waiting-room");
var pressedPlay = false;

function showWaitingRoom(){
	waitingRoom.classList.replace("invisible", "visible");
	cancelBtn.classList.replace("d-none", "d-inline");
	playBtn.classList.replace("d-inline", "d-none");
	pressedPlay = true;
}

function hideWaitingRoom(){
	waitingRoom.classList.replace("visible", "invisible");
	cancelBtn.classList.replace("d-inline", "d-none");
	playBtn.classList.replace("d-none", "d-inline");
	pressedPlay = false;
}

function play(){
	showWaitingRoom();

	// make an AJAX call to add username to match_queue table
	const xhr = new XMLHttpRequest();
	xhr.open("POST", "api/enqueue.php", true);
	xhr.send();
}

function cancel(){
	hideWaitingRoom();

	const xhr = new XMLHttpRequest();
	xhr.open("GET", "api/dequeue.php", true);
	xhr.send();

}
