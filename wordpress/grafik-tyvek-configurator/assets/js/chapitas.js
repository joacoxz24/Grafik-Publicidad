(function () {
  'use strict';
  var money = function (n) { return new Intl.NumberFormat('es-CL', {style:'currency',currency:'CLP',maximumFractionDigits:0}).format(n); };
  document.querySelectorAll('.grafik-chapitas').forEach(function (root) {
    var rules = JSON.parse(root.dataset.rules), form = root.querySelector('form');
    var kind = form.elements.kind, quantity = form.elements.quantity, button = form.querySelector('button[type="submit"]'), message = root.querySelector('.chapita-message');
    var busy = false;
    function render(changed) {
      var r = rules[kind.value], q = Number(quantity.value);
      quantity.min = r.minimum;
      if (changed && (!Number.isInteger(q) || q < r.minimum)) { quantity.value = r.minimum; q = r.minimum; }
      var valid = Number.isInteger(q) && q >= r.minimum && q <= 10000;
      var price = q >= r.threshold ? r.bulk : r.price;
      root.querySelector('.chapita-minimum').textContent = 'Mínimo ' + r.minimum + ' unidades. Puedes aumentar de una en una.';
      root.querySelector('[data-regular-range]').textContent = r.minimum + ' a ' + (r.threshold - 1) + ' unidades';
      root.querySelector('[data-regular-price]').textContent = money(r.price) + ' c/u';
      root.querySelector('[data-bulk-range]').textContent = 'Desde ' + r.threshold + ' unidades';
      root.querySelector('[data-bulk-price]').textContent = money(r.bulk) + ' c/u';
      root.querySelector('[data-unit-price]').textContent = money(price);
      root.querySelector('[data-total]').textContent = valid ? money(q * price) : 'Revisa la cantidad';
      button.disabled = busy || !valid;
    }
    kind.addEventListener('change', function () { render(true); });
    quantity.addEventListener('input', function () { render(false); });
    form.querySelector('input[type="file"]').addEventListener('change', function (event) {
      var files = Array.from(event.target.files || []);
      event.target.setCustomValidity(files.length > 3 || files.some(function (f) { return f.size > 10485760 || !/\.(png|jpe?g|pdf)$/i.test(f.name); }) ? 'Selecciona hasta 3 archivos PNG, JPG o PDF, de máximo 10 MB cada uno.' : '');
    });
    form.addEventListener('submit', async function (event) {
      event.preventDefault(); if (busy || !form.reportValidity()) return;
      busy = true; render(false); button.textContent = 'Agregando…'; message.replaceChildren();
      try {
        var response = await fetch(form.action, {method:'POST',body:new FormData(form),credentials:'same-origin'});
        var result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.data && result.data.message || 'No pudimos agregar el diseño. Inténtalo nuevamente.');
        message.textContent = 'Diseño agregado al carrito. ';
        var link = document.createElement('a'); link.href = result.data.cartUrl; link.textContent = 'Ver carrito →'; message.appendChild(link);
        document.querySelectorAll('.grafik-cart-count').forEach(function (count) { count.textContent = result.data.cartCount; });
        if (window.jQuery) window.jQuery(document.body).trigger('wc_fragment_refresh');
      } catch (error) { message.textContent = error.message || 'No pudimos conectar. Inténtalo nuevamente.'; }
      finally { busy = false; button.textContent = 'Agregar al carrito →'; render(false); }
    });
    render(false);
  });
})();
