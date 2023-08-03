class MessageArea {
	constructor(element){
		this.area = element;
	}
	checkAdmin(source){
		return source == "admin" || source == "rmwesley";
	}

	addMessage(source, content) {
		let message = document.createElement("div");

		let messageUsername = document.createElement("span");
		messageUsername.className = "chat-msg-username";
		messageUsername.innerHTML = source;

		if(this.checkAdmin(source)){
			messageUsername.classList.add("admin-user");
		}

		let messageContent = document.createElement("span");
		messageContent.className = "chat-msg-content";
		messageContent.innerHTML = content;

		message.appendChild(messageUsername);
		message.appendChild(messageContent);

		this.area.appendChild(message);
	}
}

function startChatBox() {
	chatbox = document.getElementById("chatbox");
	messageArea = new MessageArea(chatbox.querySelector(".messages"));
	typingInputField = chatbox.querySelector(".write_msg");
	sendBtn = chatbox.querySelector(".msg_send_btn");

	header = document.getElementById("chatbox-header")
	draggableChatBox(chatbox, header);

	chatSocket = new WebSocket("wss://localhost:8081");

	chatSocket.onopen = function () {
		userIdentityPromise.then((data) => {
			this.send(JSON.stringify({
				"type": "join",
				"username": data.username,
				//"guestUser": data.guestUser
			}));
		});

		messageArea.addMessage("LOCAL_SYSTEM", "This chat is not even *remotely* encrypted, so don't divulge your crimes. Go to the dark web for that.")
	}
	chatSocket.onmessage = function (event) {
		message = JSON.parse(event.data);
		messageArea.addMessage(message.source, message.content);
	}

	function sendMessage(){
		userIdentityPromise.then((data) => {
			if(typingInputField.value != ""){
				chatSocket.send(JSON.stringify({
					"type": "message",
					"username": data.username,
					"content": typingInputField.value
					//"guestUser": data.guestUser
				}));
			};
			typingInputField.value = "";
		});
	}
	sendBtn.addEventListener('click', sendMessage);
	typingInputField.onkeyup = function(event) {
		if(event.keyCode == 13) sendMessage();
	}
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
