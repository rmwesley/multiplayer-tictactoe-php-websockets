function startChatBox() {
	chatbox = document.getElementById("chatbox");
	header = document.getElementById("chatbox-header")

	draggableChatBox(chatbox, header);
}

// Make chatbox a draggable (drag and drop) element
function draggableChatBox(chatbox, header) {
	var initialX = 0, initialY = 0, deltaX = 0, deltaY = 0;

	header.onmousedown = dragMouseDown;

	function dragMouseDown(e) {
		e = e || window.event;
		e.preventDefault();

		// Get cursor position
		initialX = e.clientX;
		initialY = e.clientY;
		document.onmouseup = closeDragElement;

		// Call a function whenever the cursor moves
		document.onmousemove = elementDrag;
	}

	function elementDrag(e) {
		e = e || window.event;
		e.preventDefault();

		// Calculate the cursor displacement
		deltaX = e.clientX - initialX;
		deltaY = e.clientY - initialY;

		initialX = e.clientX;
		initialY = e.clientY;

		// Update the chatbox's position
		chatbox.style.top = (chatbox.offsetTop + deltaY) + "px";
		chatbox.style.left = (chatbox.offsetLeft + deltaX) + "px";

		// Buggy simple solution, but corner is fixed at cursor position
		//chatbox.style.left = e.clientX + "px";
		//chatbox.style.top = e.clientY + "px";
	}

	function closeDragElement() {
		// Stop moving when mouse button is released
		document.onmouseup = null;
		document.onmousemove = null;
	}
}
