(function () {
  'use strict';
  document.querySelectorAll('.grafik-order-updates').forEach(function (root) {
    var busy = false, dirty = false;
    var status = root.querySelector('[data-media-status]');
    function payload() {
      var data = {revision: root.dataset.revision, selection: {}, remove: [], tracking: {}};
      root.querySelectorAll('[data-media-id]').forEach(function (row) {
        data.selection[row.dataset.mediaId] = Array.from(row.querySelectorAll('input[value]:checked')).map(function (el) {return el.value;});
        if (row.querySelector('[data-media-remove]').checked) data.remove.push(row.dataset.mediaId);
      });
      root.querySelectorAll('[data-tracking]').forEach(function (el) {data.tracking[el.dataset.tracking] = el.value;});
      return data;
    }
    async function request(operation, file) {
      var body = new FormData();
      body.set('action', 'grafik_order_updates'); body.set('order', root.dataset.order);
      body.set('nonce', root.dataset.nonce); body.set('operation', operation);
      body.set('payload', JSON.stringify(payload()));
      if (file) body.set('file', file);
      var response = await fetch(root.dataset.url, {method: 'POST', credentials: 'same-origin', body: body});
      var result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.data && result.data.message || 'No se pudo guardar. Recarga e intenta nuevamente.');
      root.querySelector('[data-media-list]').innerHTML = result.data.html;
      if (busy) root.querySelectorAll('[data-media-list] input').forEach(function (el) {el.disabled = true;});
      root.dataset.revision = result.data.revision;
      dirty = false;
    }
    async function run(files) {
      if (busy) return;
      busy = true; status.textContent = 'Guardando…';
      root.querySelectorAll('button,input,select').forEach(function (el) {el.disabled = true;});
      try {
        await request('save');
        for (var i = 0; i < files.length; i++) {
          status.textContent = 'Subiendo archivo ' + (i + 1) + ' de ' + files.length + '…';
          await request('upload', files[i]);
        }
        status.textContent = files.length ? 'Archivos subidos. Marca los correos para cada archivo y pulsa Guardar fotos y seguimiento.' : 'Fotos y seguimiento guardados. No se enviaron correos.';
      } catch (error) { status.textContent = error.message; }
      finally {
        busy = false;
        root.querySelectorAll('button,input,select').forEach(function (el) {el.disabled = false;});
        root.querySelector('[data-media-upload]').value = '';
      }
    }
    root.addEventListener('change', function (event) {
      if (event.target.matches('[data-media-upload]')) {
        var files = Array.from(event.target.files || []);
        if (!files.length) return;
        if (files.length + root.querySelectorAll('[data-media-id]').length > 20 || files.some(function (file) {return !/\.(jpe?g|png|pdf)$/i.test(file.name) || file.size > 10485760 || !file.size;})) {
          status.textContent = 'Usa hasta 20 archivos JPG, PNG o PDF por pedido, de máximo 10 MB cada uno.';
          event.target.value = ''; return;
        }
        run(files);
      } else { dirty = true; status.textContent = 'Hay cambios sin guardar en fotos y seguimiento.'; }
    });
    root.querySelector('[data-media-save]').addEventListener('click', function () {run([]);});
    document.addEventListener('submit', function (event) {
      if ((busy || dirty) && event.target.contains(root)) {
        event.preventDefault(); event.stopImmediatePropagation();
        status.textContent = busy ? 'Espera a que termine la subida antes de actualizar el pedido.' : 'Primero pulsa Guardar fotos y seguimiento; después podrás cambiar el estado o reenviar el correo.';
        status.scrollIntoView({block: 'center'});
      }
    }, true);
    window.addEventListener('beforeunload', function (event) {if (busy || dirty) {event.preventDefault(); event.returnValue = '';}});
  });
})();
