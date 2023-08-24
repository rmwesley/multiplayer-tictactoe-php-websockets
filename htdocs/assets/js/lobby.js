// Global DOM elements
var playBtn, cancelBtn, waitingRoom, profileBtn, confirmBtn, joinModalElement, inactivityModalElement;
// Global variables
var pressedPlay;

window.onload = () => {
	startChatBox();

	playBtn = document.querySelector("#play-btn");
	cancelBtn = document.querySelector("#cancel-btn");
	waitingRoom = document.querySelector("#waiting-room");
	confirmBtn = document.querySelector("#confirm-match-btn");

	inactivityModalElement = document.querySelector('#inactive-modal');
	joinModalElement = document.querySelector('#join-modal');
	pressedPlay = false;

	joinModalElement.addEventListener('hidden.bs.modal', function () {
		cancel();
		clearTicks();
	});

	// Clicking on infobox focuses on main lobby button
	document.querySelector(".infobox").addEventListener("click", (event) =>{
		if(!pressedPlay){
			playBtn.querySelector("button").focus();
		}
		else{
			cancelBtn.querySelector("button").focus();
		}
	});
	joinModalElement.addEventListener('shown.bs.modal', function () {
		confirmBtn.focus();
	});
	joinModalElement.addEventListener("click", (event) => {
		event.stopPropagation();
		confirmBtn.focus();
	})
}

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
	waitingRoomWs.close();
}

function clearTicks(){
	document.querySelectorAll(".tick").forEach((element) => {
		element.innerHTML = "";
	});
	confirmBtn.classList.replace("disabled", false);
}
