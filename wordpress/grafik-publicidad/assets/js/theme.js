(function () {
  'use strict';
  document.querySelectorAll('[data-photo-gallery]').forEach(function (root) {
    var slides = Array.from(root.querySelectorAll('.grafik-photo-slide'));
    var controls = root.querySelector('.grafik-photo-controls');
    if (slides.length < 2 || !controls) return;
    var thumbnails = Array.from(root.querySelectorAll('[data-photo-index]'));
    var stage = root.querySelector('.grafik-photo-stage') || root;
    var strip = root.querySelector('.grafik-photo-thumbnails');
    var index = 0, requested = 0, revision = 0, hovering = false, focused = false, start = null;
    var motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    function revealThumbnail(animate) {
      if (!strip || !thumbnails[index]) return;
      var area = strip.getBoundingClientRect(), thumb = thumbnails[index].getBoundingClientRect();
      if (thumb.left < area.left || thumb.right > area.right) {
        strip.scrollTo({left:strip.scrollLeft + thumb.left - area.left - (area.width - thumb.width) / 2, behavior:animate && !motion.matches ? 'smooth' : 'instant'});
      }
    }
    if (strip && window.ResizeObserver) new ResizeObserver(function () { revealThumbnail(false); }).observe(strip);
    root.classList.add('is-enhanced');
    slides.forEach(function (slide, i) {
      slide.hidden = false;
      slide.classList.toggle('is-active', i === 0);
      slide.setAttribute('aria-hidden', i === 0 ? 'false' : 'true');
    });
    async function choose(next) {
      requested = (next + slides.length) % slides.length;
      var target = requested, request = ++revision;
      var photo = slides[target].querySelector('img');
      if (photo) {
        photo.loading = 'eager';
        if (photo.decode) { try { await photo.decode(); } catch (_) { /* Keep navigation usable if an image fails. */ } }
      }
      if (request !== revision) return;
      index = target;
      slides.forEach(function (slide, i) {
        slide.classList.toggle('is-active', i === index);
        slide.setAttribute('aria-hidden', i === index ? 'false' : 'true');
      });
      thumbnails.forEach(function (button, i) {
        if (i === index) button.setAttribute('aria-current', 'true');
        else button.removeAttribute('aria-current');
      });
      revealThumbnail(true);
      root.querySelector('[data-photo-count]').textContent = (index + 1) + ' / ' + slides.length;
    }
    controls.hidden = false;
    thumbnails.forEach(function (button, i) { button.addEventListener('click', function () { choose(i); }); });
    root.querySelector('[data-photo-prev]').addEventListener('click', function () { choose(requested - 1); });
    root.querySelector('[data-photo-next]').addEventListener('click', function () { choose(requested + 1); });
    root.addEventListener('mouseenter', function () { hovering = true; });
    root.addEventListener('mouseleave', function () { hovering = false; });
    root.addEventListener('focusin', function () { focused = true; });
    root.addEventListener('focusout', function (event) { focused = root.contains(event.relatedTarget); });
    stage.addEventListener('touchstart', function (event) { start = event.touches.length === 1 ? {x:event.touches[0].clientX, y:event.touches[0].clientY} : null; }, {passive:true});
    stage.addEventListener('touchend', function (event) {
      if (start && event.changedTouches.length) {
        var distance = event.changedTouches[0].clientX - start.x;
        var vertical = event.changedTouches[0].clientY - start.y;
        if (Math.abs(distance) > 50 && Math.abs(distance) > Math.abs(vertical)) choose(requested + (distance < 0 ? 1 : -1));
      }
      start = null;
    }, {passive:true});
    stage.addEventListener('touchcancel', function () { start = null; }, {passive:true});
    window.setInterval(function () {
      if (!motion.matches && !hovering && !focused && !document.hidden && !start) choose(requested + 1);
    }, 4000);
  });
})();

(function () {
	"use strict";

	document.querySelectorAll('a[href*="#"]').forEach(function (link) {
		link.addEventListener("click", function () {
			document.documentElement.classList.remove("grafik-menu-open");
		});
	});
})();

(function () {
  'use strict';
  document.querySelectorAll('[data-grafik-carousel]').forEach(function (root) {
    var slides = Array.from(root.querySelectorAll('.catalog-slide'));
    var buttons = Array.from(root.querySelectorAll('[data-slide]'));
    var media = window.matchMedia('(prefers-reduced-motion: reduce)');
    var index = 0, hovering = false, focused = false;
    root.querySelector('.carousel-controls').hidden = false;
    function render() {
      slides.forEach(function (slide, i) { slide.hidden = i !== index; });
      buttons.forEach(function (button, i) { if (i === index) button.setAttribute('aria-current', 'true'); else button.removeAttribute('aria-current'); });
    }
    function choose(next) { index = (next + slides.length) % slides.length; render(); }
    buttons.forEach(function (button, i) { button.addEventListener('click', function () { choose(i); }); });
    root.querySelector('[data-prev]').addEventListener('click', function () { choose(index - 1); });
    root.querySelector('[data-next]').addEventListener('click', function () { choose(index + 1); });
    root.addEventListener('mouseenter', function () { hovering = true; });
    root.addEventListener('mouseleave', function () { hovering = false; });
    root.addEventListener('focusin', function () { focused = true; });
    root.addEventListener('focusout', function (event) { if (!root.contains(event.relatedTarget)) focused = false; });
    window.setInterval(function () {
      if (!media.matches && !hovering && !focused && !document.hidden) {
        index = (index + 1) % slides.length;
        render();
      }
    }, 4000);
    render();
  });
})();
