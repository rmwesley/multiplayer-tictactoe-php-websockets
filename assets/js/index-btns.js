const loginBtn = document.querySelector('#loginForm');
const registerBtn = document.querySelector('#registerForm');

function showLoginForm() {
	loginBtn.classList.remove('d-none');
	registerBtn.classList.add('d-none');
}
function showRegisterForm() {
	registerBtn.classList.remove('d-none');
	loginBtn.classList.add('d-none');
}
