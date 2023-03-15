const playBtn = $("#play-btn");
const cancelBtn = $("#cancel-btn");
const waitingRoom = $("#waiting-room");

const profileBtn = $("#profile-btn");

usernamePromise.then((username) => {
	profileBtn.innerHTML = username;
})

var pressedPlay = false;

function showWaitingRoom(){
	waitingRoom.removeClass("invisible").addClass("visible");
	cancelBtn.removeClass("d-none").addClass("d-inline");
	playBtn.removeClass("d-inline").addClass("d-none");
	pressedPlay = true;
}

function hideWaitingRoom(){
	waitingRoom.removeClass("visible").addClass("invisible");
	cancelBtn.removeClass("d-inline").addClass("d-none");
	playBtn.removeClass("d-none").addClass("d-inline");
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
