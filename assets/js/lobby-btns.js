const playBtn = $("#play-btn");
const cancelBtn = $("#cancel-btn");
const waitingRoom = $("#waiting-room");

const profileBtn = $("#profile-btn");

const confirmBtn = $("#confirm-match-btn");

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

$('#join-modal').on('hidden.bs.modal', function () {
	$(".tick").each(function(){
		$(this).empty()
	});
	$("#confirm-match-button").prop("disabled", false);
});

function confirmMatch(){
	window.player.parent().after("<span class='col-1 tick'>✓</span>");
	// Disable button
	$("#confirm-match-button").prop("disabled", true);

	// Send confirmation message to server
	var message = {
		type: "confirm",
	};
	ws.send(JSON.stringify(message));
}
