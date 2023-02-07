// Check if the player is already in the waiting list
const xhr = new XMLHttpRequest();
xhr.open("GET", "check_queue_membership.php", true);
xhr.onreadystatechange = function() {
	if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
		const isInWaitingList = JSON.parse(this.responseText);
		if (isInWaitingList) {
			showWaitingRoom();
		}
	}
};
xhr.send();
