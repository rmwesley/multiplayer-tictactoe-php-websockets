const playBtn = document.querySelector("#playBtn");
const cancelBtn = document.querySelector("#cancelBtn");
const waitingRoom = document.querySelector("#waiting-room");
const profileBtn = document.getElementById("profile-btn");

usernamePromise.then((username) => {
	profileBtn.innerHTML = username;
})

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

	// send message to server to add user to the matchmaking queue
	usernamePromise.then((username) => {
		ws.send(JSON.stringify({ type: "enqueue", username: username }));
	});
}

function cancel(){
	hideWaitingRoom();

	// send message to server to remove user from the matchmaking queue
	ws.send(JSON.stringify({ type: "dequeue" }));
}
