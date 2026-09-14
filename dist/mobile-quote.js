(() => {
  const bar = document.querySelector('[data-mobile-quote]');
  if (!bar) return;
  const mobile = matchMedia('(max-width: 900px)');
  const menu = document.querySelector('.site-nav');
  let anchor = Math.max(0, scrollY);
  let visible = true;
  let scheduled = false;
  function render() {
    const blocked = menu?.classList.contains('open') || document.querySelector('dialog[open]');
    const show = mobile.matches && visible && !blocked;
    bar.classList.toggle('is-visible', show);
    bar.inert = !show;
    bar.setAttribute('aria-hidden', String(!show));
  }
  function scroll() {
    const y = Math.max(0, Math.min(scrollY, document.documentElement.scrollHeight - innerHeight));
    if (Math.abs(y - anchor) >= 8) {
      visible = y > anchor;
      anchor = y;
    }
    render();
    scheduled = false;
  }
  addEventListener('scroll', () => {
    if (!scheduled) { scheduled = true; requestAnimationFrame(scroll); }
  }, { passive: true });
  mobile.addEventListener('change', render);
  if (menu) new MutationObserver(render).observe(menu, { attributes: true, attributeFilter: ['class'] });
  document.querySelectorAll('dialog').forEach(dialog => new MutationObserver(render).observe(dialog, { attributes: true, attributeFilter: ['open'] }));
  render();
})();
