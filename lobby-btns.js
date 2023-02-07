const playBtn = document.querySelector("#playBtn");
const cancelBtn = document.querySelector("#cancelBtn");
const waitingRoom = document.querySelector("#waiting-room");

function play(){
	waitingRoom.classList.replace("invisible", "visible");
	cancelBtn.classList.replace("d-none", "d-inline");
	playBtn.classList.replace("d-inline", "d-none");
}

function cancel(){
	waitingRoom.classList.replace("visible", "invisible");
	cancelBtn.classList.replace("d-inline", "d-none");
	playBtn.classList.replace("d-none", "d-inline");
}
