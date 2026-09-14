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
