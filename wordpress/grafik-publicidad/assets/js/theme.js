(function () {
	"use strict";

	document.querySelectorAll('a[href*="#"]').forEach(function (link) {
		link.addEventListener("click", function () {
			document.documentElement.classList.remove("grafik-menu-open");
		});
	});
})();

