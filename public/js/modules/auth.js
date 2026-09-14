/**
 * auth.js — Módulo de autenticação.
 * Validação client-side de formulários de login e registro.
 */

const loginForm = document.querySelector('form[action="/login"]');
const registerForm = document.querySelector('form[action="/register"]');

if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
        const email = loginForm.querySelector('#email').value.trim();
        const password = loginForm.querySelector('#password').value;

        if (!email || !password) {
            e.preventDefault();
            showInlineError(loginForm, 'Preencha e-mail e senha.');
        }
    });
}

if (registerForm) {
    registerForm.addEventListener('submit', (e) => {
        const password = registerForm.querySelector('#password').value;
        const confirm  = registerForm.querySelector('#password_confirmation').value;

        if (password.length < 8) {
            e.preventDefault();
            showInlineError(registerForm, 'A senha deve ter no mínimo 8 caracteres.');
            return;
        }

        if (password !== confirm) {
            e.preventDefault();
            showInlineError(registerForm, 'As senhas não correspondem.');
        }
    });
}

function showInlineError(form, message) {
    let existing = form.querySelector('.js-inline-error');
    if (!existing) {
        existing = document.createElement('div');
        existing.className = 'alert alert--error js-inline-error';
        form.prepend(existing);
    }
    existing.textContent = message;
}
