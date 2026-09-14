/**
 * crud.js — Módulo de CRUD admin.
 * Confirmação de exclusão e ações de tabela.
 */

// Confirmação antes de qualquer form com data-confirm
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
        const message = form.dataset.confirm || 'Confirmar esta ação?';
        if (!window.confirm(message)) {
            e.preventDefault();
        }
    });
});
