(() => {
  'use strict';
  const screens = document.querySelectorAll('[data-screen]');
  const menu = document.querySelector('#main-nav');
  const menuButton = document.querySelector('.menu-button');
  let cartCount = 0;
  let toastTimer;
  const money = number => new Intl.NumberFormat('es-CL', {style:'currency', currency:'CLP', maximumFractionDigits:0}).format(number);
  const toast = message => {
    const element = document.querySelector('.toast');
    element.textContent = message;
    element.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { element.hidden = true; }, 3800);
  };
  function showView(view, scroll = true) {
    view = view === 'product' ? 'product' : 'home';
    screens.forEach(screen => { screen.hidden = screen.dataset.screen !== view; });
    document.querySelectorAll('.view-tabs [data-view]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.view === view)));
    document.querySelectorAll('[data-nav]').forEach(link => {
      if (link.dataset.nav === view) link.setAttribute('aria-current', 'page');
      else link.removeAttribute('aria-current');
    });
    document.title = (view === 'product' ? 'Chapitas' : 'Grafik Publicidad') + ' · Borrador en blanco';
    menu.classList.remove('is-open');
    menuButton.setAttribute('aria-expanded', 'false');
    if (scroll) window.scrollTo({top:0, behavior:'instant'});
  }
  document.querySelectorAll('[data-view]').forEach(control => control.addEventListener('click', event => {
    event.preventDefault();
    history.replaceState(null, '', control.dataset.view === 'product' ? '#chapitas' : '#inicio');
    showView(control.dataset.view);
  }));
  document.querySelectorAll('[data-scroll]').forEach(control => control.addEventListener('click', event => {
    event.preventDefault();
    showView('home', false);
    history.replaceState(null, '', '#' + control.dataset.scroll);
    document.getElementById(control.dataset.scroll).scrollIntoView({behavior:window.matchMedia('(prefers-reduced-motion:reduce)').matches ? 'instant' : 'smooth'});
  }));
  menuButton.addEventListener('click', () => {
    const open = menu.classList.toggle('is-open');
    menuButton.setAttribute('aria-expanded', String(open));
  });
  document.querySelector('.cart-button').addEventListener('click', () => toast(cartCount ? `Carrito de muestra: ${cartCount} diseño(s). Esta vista no realiza compras.` : 'El carrito de muestra está vacío. Prueba el configurador de Chapitas.'));
  document.querySelector('.quote-form').addEventListener('submit', event => { event.preventDefault(); toast('Borrador visual: la consulta no fue enviada.'); });

  const rules = {
    alfiler:{minimum:10, threshold:101, price:500, bulk:400},
    llavero:{minimum:5, threshold:51, price:750, bulk:650},
    destapador:{minimum:5, threshold:51, price:950, bulk:860}
  };
  const form = document.querySelector('.configurator');
  const kind = form.elements.kind;
  const quantity = form.elements.quantity;
  function renderPrice(changed) {
    const rule = rules[kind.value];
    quantity.min = rule.minimum;
    let count = Number(quantity.value);
    if (changed && count < rule.minimum) { quantity.value = rule.minimum; count = rule.minimum; }
    const valid = Number.isInteger(count) && count >= rule.minimum && count <= 10000;
    const bulk = count >= rule.threshold;
    const unit = bulk ? rule.bulk : rule.price;
    form.querySelector('[data-minimum]').textContent = `Mínimo ${rule.minimum} unidades. Aumenta de una en una.`;
    form.querySelector('[data-regular-range]').textContent = `${rule.minimum} a ${rule.threshold - 1} unidades`;
    form.querySelector('[data-bulk-range]').textContent = `Desde ${rule.threshold} unidades`;
    form.querySelector('[data-regular-price]').innerHTML = money(rule.price) + ' <small>c/u</small>';
    form.querySelector('[data-bulk-price]').innerHTML = money(rule.bulk) + ' <small>c/u</small>';
    form.querySelector('[data-unit-price]').textContent = money(unit);
    form.querySelector('[data-total]').textContent = valid ? money(unit * count) : 'Revisa la cantidad';
    form.querySelector('progress').value = valid ? Math.min(100, Math.max(0, (count - rule.minimum) / (rule.threshold - rule.minimum) * 100)) : 0;
    const message = form.querySelector('[data-progress-message]');
    if (!valid) message.textContent = 'Ingresa una cantidad válida para ver el precio.';
    else if (bulk) message.innerHTML = '<strong>¡Ya tienes el precio por mayor!</strong>';
    else message.innerHTML = `Te faltan <strong>${rule.threshold - count} unidades</strong> para el precio por mayor.`;
    const saving = form.querySelector('[data-saving]');
    saving.hidden = !valid || !bulk;
    saving.textContent = valid && bulk ? 'Ahorras ' + money((rule.price - rule.bulk) * count) + ' en este pedido.' : '';
    form.querySelector('[type="submit"]').disabled = !valid;
  }
  kind.addEventListener('change', () => renderPrice(true));
  quantity.addEventListener('input', () => renderPrice(false));
  form.addEventListener('submit', event => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    cartCount++;
    document.querySelector('[data-cart-count]').textContent = cartCount;
    toast('Agregado al carrito de muestra. No se creó ningún pedido.');
  });
  form.elements.design.addEventListener('change', event => { form.querySelector('[data-file-name]').textContent = event.target.files[0]?.name || 'Elige un archivo'; });
  renderPrice(false);

  const photos = [
    {src:'../../wordpress/grafik-publicidad/assets/images/chapitas-catalogo.png', alt:'Composición promocional de chapitas alfiler y llavero', label:'Imagen promocional'},
    {src:'https://grafikpublicidad.cl/wp/wp-content/uploads/2026/10/imagen_2026-09-30_222024371.png', alt:'Fotografía real de chapitas personalizadas', label:'Fotografía real'},
    {src:'https://grafikpublicidad.cl/wp/wp-content/uploads/2026/10/imagen_2026-09-30_221851679.png', alt:'Otro trabajo real de chapitas personalizadas', label:'Fotografía real'}
  ];
  let photoIndex = 0;
  function showPhoto(index) {
    photoIndex = (index + photos.length) % photos.length;
    const img = document.querySelector('#gallery-main');
    img.src = photos[photoIndex].src;
    img.alt = photos[photoIndex].alt;
    document.querySelector('[data-gallery-type]').textContent = photos[photoIndex].label;
    document.querySelector('[data-gallery-count]').textContent = `${photoIndex + 1} / ${photos.length}`;
    document.querySelectorAll('[data-photo]').forEach(button => button.setAttribute('aria-current', String(Number(button.dataset.photo) === photoIndex)));
    if (!window.matchMedia('(prefers-reduced-motion:reduce)').matches) img.animate([{opacity:.3},{opacity:1}], {duration:320, easing:'ease-out'});
  }
  document.querySelectorAll('[data-photo]').forEach(button => button.addEventListener('click', () => showPhoto(Number(button.dataset.photo))));
  document.querySelector('[data-gallery-prev]').addEventListener('click', () => showPhoto(photoIndex - 1));
  document.querySelector('[data-gallery-next]').addEventListener('click', () => showPhoto(photoIndex + 1));
  showView(location.hash === '#chapitas' ? 'product' : 'home', false);
})();
