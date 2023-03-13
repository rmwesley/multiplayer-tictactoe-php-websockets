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
	clientWebSocketInit();
}

function cancel(){
	hideWaitingRoom();
	ws.close();
}
