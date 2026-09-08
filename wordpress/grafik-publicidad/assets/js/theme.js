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
    var pause = root.querySelector('[data-pause]');
    var media = window.matchMedia('(prefers-reduced-motion: reduce)');
    var index = 0, stopped = media.matches, hovering = false, focused = false;
    root.querySelector('.carousel-controls').hidden = false;
    function render() {
      slides.forEach(function (slide, i) { slide.hidden = i !== index; });
      buttons.forEach(function (button, i) { if (i === index) button.setAttribute('aria-current', 'true'); else button.removeAttribute('aria-current'); });
      pause.textContent = stopped ? 'Reproducir' : 'Pausar';
      pause.setAttribute('aria-pressed', String(stopped));
    }
    function choose(next) { index = (next + slides.length) % slides.length; stopped = true; render(); }
    buttons.forEach(function (button, i) { button.addEventListener('click', function () { choose(i); }); });
    root.querySelector('[data-prev]').addEventListener('click', function () { choose(index - 1); });
    root.querySelector('[data-next]').addEventListener('click', function () { choose(index + 1); });
    pause.addEventListener('click', function () { stopped = !stopped; render(); });
    root.addEventListener('mouseenter', function () { hovering = true; });
    root.addEventListener('mouseleave', function () { hovering = false; });
    root.addEventListener('focusin', function () { focused = true; });
    root.addEventListener('focusout', function (event) { if (!root.contains(event.relatedTarget)) focused = false; });
    media.addEventListener('change', function () { stopped = media.matches; render(); });
    window.setInterval(function () { if (!stopped && !hovering && !focused && !document.hidden) { index = (index + 1) % slides.length; render(); } }, 6000);
    render();
  });
})();
