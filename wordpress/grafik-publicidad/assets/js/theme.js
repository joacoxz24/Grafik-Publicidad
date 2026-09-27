(function () {
  'use strict';
  document.querySelectorAll('[data-photo-gallery]').forEach(function (root) {
    var slides = Array.from(root.querySelectorAll('.grafik-photo-slide'));
    var controls = root.querySelector('.grafik-photo-controls');
    if (slides.length < 2 || !controls) return;
    var index = 0, hovering = false, focused = false, startX = null;
    var motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    function choose(next) {
      index = (next + slides.length) % slides.length;
      slides.forEach(function (slide, i) { slide.hidden = i !== index; });
      root.querySelector('[data-photo-count]').textContent = (index + 1) + ' / ' + slides.length;
    }
    controls.hidden = false;
    root.querySelector('[data-photo-prev]').addEventListener('click', function () { choose(index - 1); });
    root.querySelector('[data-photo-next]').addEventListener('click', function () { choose(index + 1); });
    root.addEventListener('mouseenter', function () { hovering = true; });
    root.addEventListener('mouseleave', function () { hovering = false; });
    root.addEventListener('focusin', function () { focused = true; });
    root.addEventListener('focusout', function (event) { focused = root.contains(event.relatedTarget); });
    root.addEventListener('touchstart', function (event) { startX = event.touches[0].clientX; }, {passive:true});
    root.addEventListener('touchend', function (event) {
      if (startX !== null) {
        var distance = event.changedTouches[0].clientX - startX;
        if (Math.abs(distance) > 50) choose(index + (distance < 0 ? 1 : -1));
      }
      startX = null;
    }, {passive:true});
    window.setInterval(function () {
      if (!motion.matches && !hovering && !focused && !document.hidden) choose(index + 1);
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
