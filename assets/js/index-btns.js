const loginForm = document.querySelector('#loginForm');
const registerForm = document.querySelector('#registerForm');

function showLoginForm() {
	loginForm.classList.remove('d-none');
	registerForm.classList.add('d-none');
}
function showRegisterForm() {
	registerForm.classList.remove('d-none');
	loginForm.classList.add('d-none');
}
