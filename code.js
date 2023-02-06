const n = 3;
window.onload = init;

var game = [];
function mark_x(event){
	if(event.target.tagName != "BUTTON") return;
	event.target.innerHTML = "x";
	event.target.disabled = true;
	indices = event.target.id.split(",");
	i = parseInt(indices[0])
	j = parseInt(indices[1])
	game[i][j] = 1;
}
function init(){
	var board = document.getElementById("board");
	board.addEventListener("click", mark_x)

	for (let i = 0; i < n; i++) {
		game = game.concat([[null]]);
		let newLine = document.createElement('div');
		for (let j = 0; j < n; j++) {
			game[i] = game[i].concat([null]);
			var button = document.createElement('button');
			button.innerHTML = "&nbsp;"
			button.className = "btn-sq";
			button.id = i + "," + j
			newLine.appendChild(button);
			//newLine.innerHTML += " | ";
		}
		newLine.innerHTML += "<br>";
		board.appendChild(newLine);
	}
}

