const n = 3;
window.onload = init;

//const game = new Array(3).fill().map(() => new Array(3));
const game = new Array(9);

function mark_x(event){
	if(event.target.tagName != "BUTTON") return;
	event.target.innerHTML = "x";
	event.target.disabled = true;
	index = parseInt(event.target.id);
	game[index] = 1;

	//indices = event.target.id.split(",");
	//i = parseInt(indices[0])
	//j = parseInt(indices[1])
	//game[i][j] = 1;
}

function init(){
	const board = document.getElementById("board");
	board.addEventListener("click", mark_x)
	const boardButtons = board.querySelectorAll("button");
	boardButtons.forEach((element, index) => {
		//element.id = ~~(index/n) + "," + index%n;
		element.id = index;
	})
}
