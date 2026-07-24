(function ($) {
	"use strict";

	function money(value) {
		return new Intl.NumberFormat("es-CL", {
			style: "currency",
			currency: "CLP",
			maximumFractionDigits: 0,
		}).format(value);
	}

	function initConfigurator(root) {
		const form = root.querySelector(".grafik-tyvek-form");
		if (!form) return;

		const price = Number(root.dataset.price || 10500);
		const threshold = Number(root.dataset.threshold || 1000);
		const discount = Number(root.dataset.discount || 20);
		const max = Number(root.dataset.max || 10000);
		const quantityInput = form.querySelector(".grafik-quantity-value");
		const quantityRange = form.querySelector(".grafik-quantity-range");
		const quantityLabel = form.querySelector(".grafik-quantity-label");
		const discountMessage = form.querySelector(".grafik-discount-message");
		const subtotalElement = form.querySelector(".grafik-subtotal");
		const savingRow = form.querySelector(".grafik-saving");
		const savingValue = savingRow.querySelector("strong");
		const totalElement = form.querySelector(".grafik-total");
		const colorInput = form.querySelector(".grafik-color-value");
		const colorChoice = form.querySelector(".color-choice");
		const preview = root.querySelector(".flat-band");
		const fileInput = form.querySelector(".grafik-files");
		const fileLabel = form.querySelector(".grafik-upload-label");
		const fileList = form.querySelector(".grafik-file-list");
		const status = form.querySelector(".grafik-form-status");
		const submit = form.querySelector(".grafik-add-to-cart");

		function quantity() {
			return Number(quantityInput.value || 100);
		}

		function updatePrice(nextQuantity) {
			const normalized = Math.max(100, Math.min(max, Math.round(nextQuantity / 100) * 100));
			quantityInput.value = String(normalized);
			quantityRange.value = String(normalized);
			quantityLabel.textContent = normalized.toLocaleString("es-CL") + " unidades";

			const subtotal = (normalized / 100) * price;
			const saving = normalized >= threshold ? subtotal * (discount / 100) : 0;
			subtotalElement.textContent = money(subtotal);
			totalElement.textContent = money(subtotal - saving);
			savingRow.hidden = saving <= 0;
			savingValue.textContent = saving > 0 ? "−" + money(saving) : "";

			if (normalized >= threshold) {
				discountMessage.textContent = discount + "% de descuento aplicado.";
				discountMessage.classList.add("success");
			} else {
				discountMessage.textContent =
					"Agrega " +
					(threshold - normalized).toLocaleString("es-CL") +
					" unidades para obtener " +
					discount +
					"% de descuento.";
				discountMessage.classList.remove("success");
			}
		}

		form.querySelector(".grafik-quantity-minus").addEventListener("click", function () {
			updatePrice(quantity() - 100);
		});
		form.querySelector(".grafik-quantity-plus").addEventListener("click", function () {
			updatePrice(quantity() + 100);
		});
		quantityRange.addEventListener("input", function () {
			updatePrice(Number(quantityRange.value));
		});

		form.querySelectorAll(".swatches button").forEach(function (button) {
			button.addEventListener("click", function () {
				form.querySelectorAll(".swatches button").forEach(function (item) {
					item.classList.remove("active");
				});
				button.classList.add("active");
				const name = button.dataset.color;
				colorInput.value = name;
				colorChoice.textContent = name;
				preview.className = "flat-band " + Array.from(button.classList).find(function (nameClass) {
					return nameClass !== "active";
				});
			});
		});

		fileInput.addEventListener("change", function () {
			status.textContent = "";
			const files = Array.from(fileInput.files || []);
			if (files.length > grafikTyvek.maxFiles) {
				fileInput.value = "";
				fileLabel.textContent = "Selecciona tus archivos";
				fileList.hidden = true;
				status.textContent = grafikTyvek.i18n.fileLimit;
				status.className = "grafik-form-status error";
				return;
			}
			const invalid = files.some(function (file) {
				const extension = file.name.split(".").pop().toLowerCase();
				return !grafikTyvek.fileTypes.includes(extension);
			});
			if (invalid) {
				fileInput.value = "";
				fileLabel.textContent = "Selecciona tus archivos";
				fileList.hidden = true;
				status.textContent = grafikTyvek.i18n.fileType;
				status.className = "grafik-form-status error";
				return;
			}

			fileLabel.textContent = files.length
				? files.length + " archivo" + (files.length > 1 ? "s seleccionados" : " seleccionado")
				: "Selecciona tus archivos";
			fileList.replaceChildren();
			files.forEach(function (file) {
				const item = document.createElement("li");
				item.textContent = file.name;
				fileList.appendChild(item);
			});
			fileList.hidden = files.length === 0;
		});

		form.addEventListener("submit", async function (event) {
			event.preventDefault();
			status.textContent = "";
			status.className = "grafik-form-status";
			submit.disabled = true;
			submit.childNodes[0].nodeValue = grafikTyvek.i18n.adding + " ";

			try {
				const response = await fetch(grafikTyvek.ajaxUrl, {
					method: "POST",
					body: new FormData(form),
					credentials: "same-origin",
				});
				const payload = await response.json();
				if (!response.ok || !payload.success) {
					throw new Error(payload.data && payload.data.message ? payload.data.message : grafikTyvek.i18n.connection);
				}

				const data = payload.data;
				if (data.fragments) {
					Object.keys(data.fragments).forEach(function (selector) {
						document.querySelectorAll(selector).forEach(function (element) {
							const wrapper = document.createElement("div");
							wrapper.innerHTML = data.fragments[selector];
							const replacement = wrapper.firstElementChild;
							if (replacement) element.replaceWith(replacement);
						});
					});
				}
				$(document.body).trigger("added_to_cart", [data.fragments || {}, data.cartHash || "", submit]);
				status.className = "grafik-form-status success";
				status.innerHTML =
					"<span>" +
					grafikTyvek.i18n.added +
					'</span><a href="' +
					data.cartUrl +
					'">' +
					grafikTyvek.i18n.viewCart +
					" →</a>";
			} catch (error) {
				status.className = "grafik-form-status error";
				status.textContent = error.message || grafikTyvek.i18n.connection;
			} finally {
				submit.disabled = false;
				submit.childNodes[0].nodeValue = grafikTyvek.i18n.add + " ";
			}
		});

		updatePrice(100);
	}

	document.querySelectorAll(".grafik-tyvek").forEach(initConfigurator);

	function toggleShippingFields() {
		const selected = document.querySelector('input[name="grafik_delivery_method"]:checked');
		const extra = document.querySelector(".grafik-shipping-extra");
		if (!extra || !selected) return;
		const transport = selected.value === "transport";
		extra.hidden = !transport;
		extra.querySelectorAll("input, select").forEach(function (field) {
			field.required = transport;
		});
	}

	document.addEventListener("change", function (event) {
		if (event.target && event.target.name === "grafik_delivery_method") {
			toggleShippingFields();
		}
	});
	toggleShippingFields();
})(jQuery);

