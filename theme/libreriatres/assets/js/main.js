/**
 * Libreria Tres - interacciones del sitio.
 */
(function () {
	'use strict';

	var doc = document;

	/* ---------------------------------------------------------- Menu mobile */
	var openBtn = doc.querySelector('[data-menu-open]');
	var closeBtn = doc.querySelector('[data-menu-close]');
	var menu = doc.getElementById('mobileMenu');

	function setMenu(open) {
		if (!menu) return;
		menu.hidden = !open;
		doc.body.style.overflow = open ? 'hidden' : '';
		if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	if (openBtn) {
		openBtn.addEventListener('click', function () {
			setMenu(menu && menu.hidden);
		});
	}
	if (closeBtn) {
		closeBtn.addEventListener('click', function () {
			setMenu(false);
		});
	}
	if (menu) {
		menu.addEventListener('click', function (e) {
			if (e.target === menu) setMenu(false);
		});
	}
	doc.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') setMenu(false);
	});

	/* --------------------------------------------------------- Favoritos */
	var WISH_KEY = 'libreriatres:favs';

	function readFavs() {
		try {
			return JSON.parse(localStorage.getItem(WISH_KEY) || '[]');
		} catch (e) {
			return [];
		}
	}

	function writeFavs(list) {
		try {
			localStorage.setItem(WISH_KEY, JSON.stringify(list));
		} catch (e) {
			/* sin persistencia */
		}
	}

	function favKey(btn) {
		var card = btn.closest('.product-card');
		var link = card ? card.querySelector('.product-card__title a') : null;
		return link ? link.getAttribute('href') : btn.getAttribute('data-key') || btn.outerHTML.length;
	}

	function refreshFavs() {
		var favs = readFavs();
		doc.querySelectorAll('[data-wish]').forEach(function (btn) {
			if (favs.indexOf(favKey(btn)) !== -1) btn.classList.add('is-active');
		});
	}

	doc.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('[data-wish]') : null;
		if (!btn) return;
		e.preventDefault();
		var key = favKey(btn);
		var favs = readFavs();
		var i = favs.indexOf(key);
		if (i === -1) {
			favs.push(key);
			btn.classList.add('is-active');
		} else {
			favs.splice(i, 1);
			btn.classList.remove('is-active');
		}
		writeFavs(favs);
	});

	refreshFavs();

	/* ------------------------------- Estado "en el carrito" de las cards */
	/* El HTML puede venir cacheado (LiteSpeed): el estado se consulta por AJAX. */
	function refreshCartState() {
		var url = window.libreriatresData && window.libreriatresData.cartItems;
		if (!url) return;

		fetch(url, { credentials: 'same-origin' })
			.then(function (r) {
				return r.json();
			})
			.then(function (data) {
				if (!data || !data.success || !data.data) return;

				var ids = data.data.ids || [];

				doc.querySelectorAll('.product-card__in-cart').forEach(function (link) {
					var id = parseInt(link.getAttribute('data-product-id'), 10);
					var inCart = ids.indexOf(id) !== -1;

					link.classList.toggle('is-hidden', !inCart);

					var form = link.parentNode.querySelector(
						'.product-card__cart[data-product-id="' + id + '"]'
					);
					if (form) form.classList.toggle('is-hidden', inCart);
				});
			})
			.catch(function () {
				/* si falla, queda el boton de agregar */
			});
	}

	refreshCartState();

	/* ------------------------------------------- Pedido por WhatsApp (carrito) */
	var orderBtn = doc.querySelector('[data-wa-order]');

	function clean(text) {
		return (text || '').replace(/\u00a0/g, ' ').replace(/\s+/g, ' ').trim();
	}

	if (orderBtn) {
		orderBtn.addEventListener('click', function (e) {
			e.preventDefault();

			var rows = doc.querySelectorAll('.woocommerce-cart-form__cart-item, .shop_table.cart tbody tr');
			var lines = [];
			var total = '';

			rows.forEach(function (row) {
				var nameEl = row.querySelector('.product-name a, td.product-name a');
				if (!nameEl) return;

				var name = clean(nameEl.textContent);
				var qtyEl = row.querySelector('.product-quantity input, td.product-quantity input');
				var qty = qtyEl ? (qtyEl.value || '1').trim() : '1';

				var lineEl = row.querySelector('.product-subtotal, td.product-subtotal');
				var priceEl = row.querySelector('.product-price, td.product-price');
				var price = clean(lineEl ? lineEl.textContent : (priceEl ? priceEl.textContent : ''));

				lines.push('• ' + name + ' x' + qty + (price ? ' — ' + price : ''));
			});

			if (!lines.length) {
				return;
			}

			var totalEl = doc.querySelector('.cart_totals .order-total .amount, .order-total .amount, td.order-total .amount');
			if (totalEl) total = clean(totalEl.textContent);

			var url = (window.libreriatresData && window.libreriatresData.cartUrl) || window.location.href;

			var msg = '¡Hola! Quiero hacer este pedido en Librería Tres:\n\n' + lines.join('\n');
			if (total) msg += '\n\nTotal: ' + total;
			msg += '\n\n¿Me confirmás disponibilidad, forma de pago y envío?';
			msg += '\n\n' + url;

			var number = (window.libreriatresData && window.libreriatresData.whatsapp) || '';
			window.open('https://wa.me/' + number + '?text=' + encodeURIComponent(msg), '_blank', 'noopener');
		});
	}

	/* --------------------------------------------- Boton flotante WhatsApp */
	var floatBtn = doc.querySelector('[data-wa-float]');
	if (floatBtn) {
		var number = (window.libreriatresData && window.libreriatresData.whatsapp) || '';
		floatBtn.setAttribute(
			'href',
			'https://wa.me/' + number + '?text=' + encodeURIComponent('¡Hola! Tengo una consulta sobre productos de Libreria Tres.')
		);
		floatBtn.setAttribute('target', '_blank');
		floatBtn.setAttribute('rel', 'noopener');
	}

	/* ------------------------------------------- Slug del producto a WhatsApp */
	doc.querySelectorAll('a[data-wa][href*="wa.me"]').forEach(function (a) {
		a.addEventListener('click', function () {
			/* el href ya viene con el mensaje prearmado por PHP */
		});
	});
})();
