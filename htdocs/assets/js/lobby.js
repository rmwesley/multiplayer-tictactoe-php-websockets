// Global DOM elements
var playBtn, cancelBtn, waitingRoom, profileBtn, confirmBtn;
// Global variables
var pressedPlay;

window.onload = () => {
	startChatBox();
	playBtn = $("#play-btn");
	cancelBtn = $("#cancel-btn");
	waitingRoom = $("#waiting-room");
	confirmBtn = $("#confirm-match-btn");

	inactivityModal = $('#inactive-modal');
	joinModal = $('#join-modal');

	pressedPlay = false;

	//$(document).ready(function(){
	//	$(this).click(function() {
	//	   var activeElement = document.activeElement;
	//	   console.log(activeElement.tagName, activeElement.type || 'N/A');
	//	 });
	//   });

	// Enter is equivalent to click on Play/Confirm
	$(document).keyup((event) => {
		var activeElement = document.activeElement;
		if(activeElement.querySelector('#play-btn') === null) return;
		if(activeElement.querySelector('#cancel-btn') === null) return;
		if (event.keyCode == 13) {
			if(pressedPlay){
				cancelBtn.children().first().click();
			}
			else playBtn.children().first().click();
		}
	});
}

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
	cancel();
	$(".tick").each(function(){
		$(this).empty()
	});
	$("#confirm-match-button").prop("disabled", false);
});

function confirmMatch(){
	playerElement.parent().after("<span class='col-1 tick'>✓</span>");
	// Disable button
	$("#confirm-match-button").prop("disabled", true);

	// Send confirmation message to server
	var message = {
		type: "confirm",
	};
	ws.send(JSON.stringify(message));
}
