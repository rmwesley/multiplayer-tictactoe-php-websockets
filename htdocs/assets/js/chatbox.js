class MessageArea {
	constructor(element){
		this.area = element;
		this.date = null;
	}
	checkAdmin(source){
		return source == "admin" || source == "rmwesley";
	}
	addDateDivider() {
		let date = document.createElement("div");
		date.classList.add("date");

		let dateText = document.createElement("span");
		dateText.classList.add("date-string");
		dateText.classList.add("bg-body-secondary");
		dateText.innerHTML = this.date;

		date.appendChild(dateText);

		this.area.appendChild(date);
	}

	addSystemMessage(source, content) {
		let message = document.createElement("div");
		message.classList.add("message");

		let messageUsername = document.createElement("span");
		messageUsername.className = "chat-msg-username";
		messageUsername.classList.add("local");
		messageUsername.innerHTML = source;

		let messageContent = document.createElement("span");
		messageContent.className = "chat-msg-content";
		messageContent.innerHTML = content;

		message.appendChild(messageUsername);
		message.appendChild(messageContent);

		this.area.appendChild(message);
	}
	addMessage(source, content, time) {
		let message = document.createElement("div");
		message.classList.add("message");

		let messageUsername = document.createElement("span");
		messageUsername.className = "chat-msg-username";
		messageUsername.innerHTML = source;

		if(this.checkAdmin(source)){
			messageUsername.classList.add("admin-user");
		}

		let messageContent = document.createElement("span");
		messageContent.className = "chat-msg-content";
		messageContent.innerHTML = content;

		let msgDate = new Date(1000 * time);

		let messageTime = document.createElement("span");
		messageTime.className = "chat-msg-time";
		messageTime.innerHTML = msgDate.toLocaleTimeString("pt-BR");

		let dateString = msgDate.getDate() + "/" + (msgDate.getMonth() + 1) + "/" + msgDate.getFullYear();

		if(this.date !== dateString){
			this.date = dateString;
			this.addDateDivider();
		}
		message.appendChild(messageUsername);
		message.appendChild(messageContent);
		message.appendChild(messageTime);

		this.area.appendChild(message);
	}
}

function startChatBox() {
	chatbox = document.getElementById("chatbox");
	messagesElement = chatbox.querySelector(".messages");
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

		messageArea.addSystemMessage("LOCAL_SYSTEM", "This chat is not even *remotely* encrypted, so don't go divulging your crimes. Go to the dark web for that.")
	}
	chatSocket.onmessage = function (event) {
		message = JSON.parse(event.data);
		if(message.type == "new_message"){
			messageArea.addMessage(message.source, message.content, message.time);
		}
		if(message.type == "history"){
			message.history.forEach((chat_msg) => {
				if(chat_msg == null) return;
				messageArea.addMessage(chat_msg[0], chat_msg[1], chat_msg[2]);
			});
		}
		messagesElement.scrollTo(0, messagesElement.scrollHeight);
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
	header.ontouchstart = dragTouchStart;

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
	function dragTouchStart(e) {
		// Get cursor position
		initialX = e.touches[0].clientX;
		initialY = e.touches[0].clientY;

		document.addEventListener("touchend", closeTouch);

		//document.ontouchmove = (event) => {
		document.addEventListener("touchmove", elementDragTouch);
	}

	function elementDrag(e) {
		// Calculate the cursor displacement
		deltaX = e.clientX - initialX;
		deltaY = e.clientY - initialY;

		initialX = e.clientX;
		initialY = e.clientY;

		// Update the chatbox's position
		chatbox.style.top = (chatbox.offsetTop + deltaY) + "px";
		chatbox.style.left = (chatbox.offsetLeft + deltaX) + "px";
	}

	function elementDragTouch(event) {
		elementDrag(event.touches[0]);
	}
	function closeDragElement() {
		// Stop moving when mouse button is released
		document.onmouseup = null;
		document.onmousemove = null;
	}
	function closeTouch() {
		document.removeEventListener("touchend", closeTouch);
		document.removeEventListener("touchmove", elementDragTouch);
	}
}
