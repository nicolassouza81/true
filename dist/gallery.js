const gallery = document.querySelector('[data-gallery]');
const viewer = document.querySelector('[data-viewer]');
const stage = viewer.querySelector('[data-stage]');
const caption = viewer.querySelector('[data-caption]');
let activeCard;
function closeViewer() {
  stage.querySelector('video')?.pause();
  viewer.close();
}
gallery.addEventListener('click', (event) => {
  const card = event.target.closest('[data-media]');
  if (!card) return;
  activeCard = card;
  stage.replaceChildren();
  const media = document.createElement(card.dataset.type === 'video' ? 'video' : 'img');
  media.src = card.href;
  if (card.dataset.type === 'video') {
    media.controls = true;
    media.playsInline = true;
    media.poster = card.querySelector('img').src;
  } else media.alt = card.querySelector('img').alt;
  stage.append(media);
  caption.textContent = card.querySelector('figcaption').textContent;
  event.preventDefault();
  viewer.showModal();
  document.body.style.overflow = 'hidden';
});
viewer.querySelector('[data-close]').addEventListener('click', closeViewer);
viewer.addEventListener('click', (event) => { if (event.target === viewer) closeViewer(); });
viewer.addEventListener('close', () => {
  stage.querySelector('video')?.pause();
  stage.replaceChildren();
  document.body.style.overflow = '';
  activeCard?.focus();
});
document.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', () => {
  document.querySelectorAll('[data-filter]').forEach(b => b.setAttribute('aria-pressed', String(b === button)));
  gallery.querySelectorAll('[data-media]').forEach(card => { card.hidden = button.dataset.filter !== 'all' && card.dataset.type !== button.dataset.filter; });
}));
