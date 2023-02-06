const playBtn = document.querySelector("#playBtn");
const cancelBtn = document.querySelector("#cancelBtn");

function play(){
	cancelBtn.classList.replace("d-none", "d-inline");
	playBtn.classList.replace("d-inline", "d-none");
}

function cancel(){
	cancelBtn.classList.replace("d-inline", "d-none");
	playBtn.classList.replace("d-none", "d-inline");
}
