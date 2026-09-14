/**
 * main.js — Entry point JS da aplicação.
 * Importa módulos por página (lazy por rota).
 * Não usa bundler — import nativo ES6 via <script type="module">.
 */

const path = window.location.pathname;

// ---- Auto-dismiss de alertas após 4 segundos ----
document.querySelectorAll('.alert').forEach((el) => {
    setTimeout(() => {
        el.style.transition = 'opacity 0.4s ease';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 400);
    }, 4000);
});

// ---- Imports por página ----
if (path === '/') {
    import('./modules/carousel.js').then(({ initTestimonialsCarousel }) => {
        initTestimonialsCarousel();
    });
}

if (path.startsWith('/admin')) {
    import('./modules/crud.js');
}

if (path === '/admin/pipeline') {
    import('./modules/pipeline.js').then(({ initPipelineBoard }) => {
        initPipelineBoard();
    });
}

if (path.startsWith('/billing') || path === '/plans') {
    import('./modules/billing.js');
}

if (path === '/login' || path === '/register') {
    import('./modules/auth.js');
}
