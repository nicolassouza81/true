/**
 * billing.js — Módulo de cobrança/assinatura.
 * Feedback visual no checkout (evita double-submit).
 */

document.querySelectorAll('form[action^="/billing/checkout"]').forEach((form) => {
    form.addEventListener('submit', () => {
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Aguarde...';
        }
    });
});
