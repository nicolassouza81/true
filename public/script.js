const header = document.querySelector('[data-header]');
const progress = document.querySelector('.scroll-progress span');
const video = document.querySelector('.hero-video');
const guideImage = document.querySelector('.visual-frame img');
const solutionHero = document.querySelector('.solution-hero');
const menuButton = document.querySelector('.menu-toggle');
const menu = document.querySelector('.site-nav');
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.querySelector('[data-year]').textContent = new Date().getFullYear();

function updateScroll() {
  const y = window.scrollY;
  const height = document.documentElement.scrollHeight - window.innerHeight;
  progress.style.transform = `scaleX(${height > 0 ? y / height : 0})`;
  header.classList.toggle('scrolled', y > 40);

  if (!reducedMotion) {
    if (video && y < window.innerHeight * 1.2) {
      video.style.transform = `scale(${1.05 + y * 0.00008}) translateY(${y * 0.11}px)`;
    }
    if (guideImage) {
      const box = guideImage.parentElement.getBoundingClientRect();
      const offset = Math.max(-1, Math.min(1, (window.innerHeight / 2 - box.top - box.height / 2) / window.innerHeight));
      guideImage.style.transform = `translateY(${offset * 6}%) scale(1.04)`;
    }
    if (solutionHero && y < window.innerHeight * 1.2) {
      solutionHero.style.setProperty('--hero-y', `${y * .08}px`);
    }
  }
}

let ticking = false;
window.addEventListener('scroll', () => {
  if (!ticking) {
    requestAnimationFrame(() => { updateScroll(); ticking = false; });
    ticking = true;
  }
}, { passive: true });
updateScroll();

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.14, rootMargin: '0px 0px -7% 0px' });

document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));

menuButton.addEventListener('click', () => {
  const isOpen = menu.classList.toggle('open');
  menuButton.setAttribute('aria-expanded', String(isOpen));
  document.body.style.overflow = isOpen ? 'hidden' : '';
});

menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
  menu.classList.remove('open');
  menuButton.setAttribute('aria-expanded', 'false');
  document.body.style.overflow = '';
}));

if (!reducedMotion && window.matchMedia('(pointer: fine)').matches) {
  document.querySelectorAll('.magnetic').forEach((button) => {
    button.addEventListener('mousemove', (event) => {
      const box = button.getBoundingClientRect();
      button.style.transform = `translate(${(event.clientX - box.left - box.width / 2) * .08}px, ${(event.clientY - box.top - box.height / 2) * .12}px)`;
    });
    button.addEventListener('mouseleave', () => { button.style.transform = ''; });
  });
}
