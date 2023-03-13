const lobby = $('#waiting-room').parent();

// Fetching the popup HTML contents, it is a BootStrap Modal div
var popupPromise = fetch('views/inactivity-popup.html')
	.then(response => response.text())
	.then((popupDiv) => {
		lobby.append(popupDiv);

		return lobby.find('#popup-modal');
	})

// Show the popup Modal and hide the Waiting Room
function showInactivityPopup() {
	popupPromise.then((modal) => {
		$('#popup-modal').modal('show');
		hideWaitingRoom();
	});
}

