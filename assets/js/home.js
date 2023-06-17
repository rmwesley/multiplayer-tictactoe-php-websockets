const loginForm = document.querySelector('#loginForm');
const registerForm = document.querySelector('#registerForm');

// Password and Confirmed password validation
function validateForm(){
	let passwordList = registerForm.querySelectorAll("input[type='password']");
	if(passwordList[0].value == passwordList[1].value) return true;

}
function showLoginForm() {
	loginForm.classList.remove('d-none');
	registerForm.classList.add('d-none');
}
function showRegisterForm() {
	registerForm.classList.remove('d-none');
	loginForm.classList.add('d-none');
}
