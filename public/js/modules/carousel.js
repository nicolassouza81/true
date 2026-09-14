/**
 * carousel.js — Carrossel de testemunhos com navegação por botões e dots.
 * Sem dependências externas. Suporta touch/swipe em mobile.
 */

export function initTestimonialsCarousel() {
    const track   = document.getElementById('testimonialsTrack');
    const prevBtn = document.getElementById('testimonialsPrev');
    const nextBtn = document.getElementById('testimonialsNext');
    const dotsEl  = document.getElementById('testimonialsDots');

    if (!track) return;

    const cards     = Array.from(track.children);
    const totalCards = cards.length;
    let current     = 0;
    let autoTimer   = null;
    let isHovered   = false;

    // Calcula quantos cards cabem visíveis ao mesmo tempo
    function visibleCount() {
        const trackWidth = track.parentElement.offsetWidth;
        const cardWidth  = cards[0]?.offsetWidth ?? 300;
        return Math.max(1, Math.floor(trackWidth / (cardWidth + 24))); // 24 = gap
    }

    function maxIndex() {
        return Math.max(0, totalCards - visibleCount());
    }

    function goTo(index) {
        current = Math.max(0, Math.min(index, maxIndex()));

        const card = cards[current];
        if (!card) return;

        track.scrollTo({ left: card.offsetLeft, behavior: 'smooth' });

        // Dots
        document.querySelectorAll('.carousel-dot').forEach((dot, i) => {
            dot.classList.toggle('carousel-dot--active', i === current);
        });

        // Botões
        if (prevBtn) prevBtn.disabled = current === 0;
        if (nextBtn) nextBtn.disabled = current >= maxIndex();
    }

    function next() { goTo(current + 1 > maxIndex() ? 0 : current + 1); }
    function prev() { goTo(current - 1 < 0 ? maxIndex() : current - 1); }

    // Auto-play
    function startAuto() {
        autoTimer = setInterval(() => {
            if (!isHovered) next();
        }, 5000);
    }

    function stopAuto() {
        clearInterval(autoTimer);
    }

    // Eventos de botão
    if (prevBtn) prevBtn.addEventListener('click', () => { stopAuto(); prev(); startAuto(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { stopAuto(); next(); startAuto(); });

    // Dots
    dotsEl?.querySelectorAll('.carousel-dot').forEach((dot) => {
        dot.addEventListener('click', () => {
            stopAuto();
            goTo(Number(dot.dataset.index));
            startAuto();
        });
    });

    // Hover pausa auto-play
    track.parentElement.addEventListener('mouseenter', () => { isHovered = true; });
    track.parentElement.addEventListener('mouseleave', () => { isHovered = false; });

    // Touch / swipe
    let touchStartX = 0;
    let touchEndX   = 0;

    track.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        stopAuto();
    }, { passive: true });

    track.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 50) {
            diff > 0 ? next() : prev();
        }
        startAuto();
    }, { passive: true });

    // Teclado (acessibilidade)
    document.addEventListener('keydown', (e) => {
        const carousel = document.getElementById('testimonialsCarousel');
        if (!carousel) return;
        if (e.key === 'ArrowLeft')  { stopAuto(); prev(); startAuto(); }
        if (e.key === 'ArrowRight') { stopAuto(); next(); startAuto(); }
    });

    // Recalcula ao redimensionar
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => goTo(current), 200);
    });

    // Init
    goTo(0);
    startAuto();
}
