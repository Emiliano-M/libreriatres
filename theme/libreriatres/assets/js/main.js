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

				// Si tiene atributos de variante en una lista <dl class="variation">, los sumamos
				var varDl = row.querySelector('dl.variation');
				if (varDl) {
					var varParts = [];
					var dts = varDl.querySelectorAll('dt');
					var dds = varDl.querySelectorAll('dd');
					dts.forEach(function (dt, idx) {
						var k = clean(dt.textContent).replace(/:$/, '');
						var v = dds[idx] ? clean(dds[idx].textContent) : '';
						if (k && v) varParts.push(k + ': ' + v);
					});
					if (varParts.length) {
						name += ' (' + varParts.join(', ') + ')';
					}
				}

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

	/* ------------------------------------------------------ Buscador en vivo */
	var searchForms = doc.querySelectorAll('[data-live-search]');
	var ajaxUrl = (window.libreriatresData && window.libreriatresData.ajaxUrl) || '/wp-admin/admin-ajax.php';

	searchForms.forEach(function (form) {
		var input = form.querySelector('.header-search__input');
		var resultsBox = form.querySelector('.header-search__results');
		var clearBtn = form.querySelector('.header-search__clear');
		if (!input || !resultsBox) return;

		var currentAbort = null;
		var debounceTimer = null;
		var activeIndex = -1;
		var cache = {};

		function setLoading(loading) {
			form.classList.toggle('is-loading', loading);
		}

		function showResults() {
			resultsBox.hidden = false;
			form.classList.add('is-open');
		}

		function hideResults() {
			resultsBox.hidden = true;
			form.classList.remove('is-open');
			activeIndex = -1;
		}

		function updateClear() {
			if (clearBtn) {
				clearBtn.hidden = !input.value.trim();
			}
		}

		function escapeHtml(str) {
			var div = doc.createElement('div');
			div.textContent = str || '';
			return div.innerHTML;
		}

		function highlight(text, term) {
			if (!term) return escapeHtml(text);
			var safeText = escapeHtml(text);
			var safeTerm = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
			try {
				var re = new RegExp('(' + safeTerm + ')', 'gi');
				return safeText.replace(re, '<mark class="header-search__match">$1</mark>');
			} catch (e) {
				return safeText;
			}
		}

		function render(data, term) {
			var products = (data && data.products) || [];
			var total = (data && data.total) || 0;

			if (!products.length) {
				resultsBox.innerHTML =
					'<div class="header-search__empty">' +
					'<p class="header-search__empty-text">No encontramos productos para "<strong>' + escapeHtml(term) + '</strong>"</p>' +
					'<span class="header-search__empty-tip">Probá con palabras más generales (ej: cuaderno, marcador, repuesto).</span>' +
					'</div>';
				showResults();
				return;
			}

			var html = '<ul class="header-search__list" role="listbox">';
			products.forEach(function (p, idx) {
				var thumbHtml = p.thumb
					? '<img src="' + escapeHtml(p.thumb) + '" alt="" loading="lazy">'
					: '<span class="header-search__thumb-placeholder">✏️</span>';

				html +=
					'<li class="header-search__item" role="option" id="search-opt-' + idx + '" data-index="' + idx + '">' +
					'<a class="header-search__link" href="' + escapeHtml(p.url) + '">' +
					'<div class="header-search__thumb">' + thumbHtml + '</div>' +
					'<div class="header-search__info">' +
					'<span class="header-search__title">' + highlight(p.title, term) + '</span>' +
					(p.category ? '<span class="header-search__category">' + escapeHtml(p.category) + '</span>' : '') +
					'</div>' +
					'<div class="header-search__price">' + (p.price_html || '') + '</div>' +
					'</a>' +
					'</li>';
			});
			html += '</ul>';

			if (total > products.length && data.view_all_url) {
				html +=
					'<a class="header-search__view-all" href="' + escapeHtml(data.view_all_url) + '">' +
					'<span>Ver todos los resultados (' + total + ')</span>' +
					'<span class="header-search__view-all-arrow" aria-hidden="true">→</span>' +
					'</a>';
			}

			resultsBox.innerHTML = html;
			showResults();
		}

		function fetchResults(term) {
			var cleanTerm = term.trim();
			if (cleanTerm.length < 2) {
				hideResults();
				setLoading(false);
				return;
			}

			var cacheKey = cleanTerm.toLowerCase();
			if (cache[cacheKey]) {
				render(cache[cacheKey], cleanTerm);
				setLoading(false);
				return;
			}

			if (currentAbort) {
				currentAbort.abort();
			}
			currentAbort = new AbortController();

			setLoading(true);

			var url = ajaxUrl + (ajaxUrl.indexOf('?') === -1 ? '?' : '&') +
				'action=libreriatres_live_search&term=' + encodeURIComponent(cleanTerm);

			fetch(url, { signal: currentAbort.signal, credentials: 'same-origin' })
				.then(function (res) {
					return res.json();
				})
				.then(function (res) {
					setLoading(false);
					if (res && res.success && res.data) {
						cache[cacheKey] = res.data;
						render(res.data, cleanTerm);
					}
				})
				.catch(function (err) {
					if (err && err.name === 'AbortError') return;
					setLoading(false);
				});
		}

		input.addEventListener('input', function () {
			updateClear();
			clearTimeout(debounceTimer);
			var term = input.value;
			if (term.trim().length < 2) {
				hideResults();
				setLoading(false);
				return;
			}
			debounceTimer = setTimeout(function () {
				fetchResults(term);
			}, 250);
		});

		input.addEventListener('focus', function () {
			updateClear();
			if (input.value.trim().length >= 2) {
				fetchResults(input.value);
			}
		});

		if (clearBtn) {
			clearBtn.addEventListener('click', function () {
				input.value = '';
				updateClear();
				hideResults();
				input.focus();
			});
		}

		// Navegación con flechas del teclado
		input.addEventListener('keydown', function (e) {
			var items = resultsBox.querySelectorAll('.header-search__item');
			if (!items.length || resultsBox.hidden) {
				if (e.key === 'Escape') hideResults();
				return;
			}

			if (e.key === 'ArrowDown') {
				e.preventDefault();
				activeIndex = (activeIndex + 1) % items.length;
				updateActive(items);
			} else if (e.key === 'ArrowUp') {
				e.preventDefault();
				activeIndex = (activeIndex - 1 + items.length) % items.length;
				updateActive(items);
			} else if (e.key === 'Enter') {
				if (activeIndex >= 0 && items[activeIndex]) {
					e.preventDefault();
					var link = items[activeIndex].querySelector('.header-search__link');
					if (link) link.click();
				}
			} else if (e.key === 'Escape') {
				hideResults();
			}
		});

		function updateActive(items) {
			items.forEach(function (it, idx) {
				var isActive = idx === activeIndex;
				it.classList.toggle('is-selected', isActive);
				if (isActive) {
					it.scrollIntoView({ block: 'nearest' });
					it.setAttribute('aria-selected', 'true');
				} else {
					it.removeAttribute('aria-selected');
				}
			});
		}

		// Cerrar al hacer click afuera
		doc.addEventListener('click', function (e) {
			if (!form.contains(e.target)) {
				hideResults();
			}
		});
	});

	// Toggle para buscador en mobile
	var searchToggles = doc.querySelectorAll('[data-search-toggle]');
	searchToggles.forEach(function (toggle) {
		toggle.addEventListener('click', function (e) {
			e.preventDefault();
			var targetId = toggle.getAttribute('data-target') || 'headerSearchMobile';
			var target = doc.getElementById(targetId);
			if (!target) return;
			var isHidden = target.hidden;
			target.hidden = !isHidden;
			toggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
			toggle.classList.toggle('is-active', isHidden);
			if (isHidden) {
				var inp = target.querySelector('.header-search__input');
				if (inp) {
					setTimeout(function () {
						inp.focus();
					}, 120);
				}
			}
		});
	});

	/* ----------------------------------- Variantes con botones (pills/chips) */
	var COLOR_MAP = {
		rosa: '#f48fb1',
		'rosa pastel': '#f8bbd0',
		fucsia: '#e91e63',
		magenta: '#c2185b',
		lila: '#ce93d8',
		violeta: '#9c27b0',
		morado: '#7b1fa2',
		lavanda: '#d1c4e9',
		celeste: '#81d4fa',
		azul: '#1e88e5',
		'azul marino': '#1a237e',
		turquesa: '#26c6da',
		aqua: '#4dd0e1',
		verde: '#43a047',
		'verde agua': '#80cbc4',
		menta: '#a5d6a7',
		amarillo: '#fdd835',
		'amarillo pastel': '#fff59d',
		naranja: '#fb8c00',
		coral: '#ff7043',
		salmon: '#ff8a65',
		durazno: '#ffccbc',
		rojo: '#e53935',
		bordo: '#880e4f',
		negro: '#262626',
		blanco: '#ffffff',
		gris: '#9e9e9e',
		plata: '#cfd8dc',
		plateado: '#cfd8dc',
		dorado: '#d4af37',
		oro: '#d4af37',
		beige: '#e6ccb2',
		natural: '#ede0d4',
		marron: '#795548',
		kraft: '#c19a6b',
		pastel: 'linear-gradient(135deg, #f8bbd0 0%, #ce93d8 50%, #81d4fa 100%)',
		multicolor: 'linear-gradient(135deg, #ff7043 0%, #fdd835 35%, #43a047 70%, #1e88e5 100%)'
	};

	function normalizeColorKey(str) {
		return (str || '')
			.toLowerCase()
			.normalize('NFD')
			.replace(/[\u0300-\u036f]/g, '')
			.trim();
	}

	function resolveSwatchColor(attrLabel, optionText, optionVal) {
		var attrKey = normalizeColorKey(attrLabel);
		var isColorAttr =
			attrKey.indexOf('color') !== -1 ||
			attrKey.indexOf('tono') !== -1;

		var textKey = normalizeColorKey(optionText);
		var valKey = normalizeColorKey(optionVal).replace(/-/g, ' ');

		if (COLOR_MAP[textKey]) return COLOR_MAP[textKey];
		if (COLOR_MAP[valKey]) return COLOR_MAP[valKey];

		if (isColorAttr) {
			var keys = Object.keys(COLOR_MAP);
			for (var i = 0; i < keys.length; i++) {
				if (textKey.indexOf(keys[i]) !== -1 || valKey.indexOf(keys[i]) !== -1) {
					return COLOR_MAP[keys[i]];
				}
			}
		}
		return '';
	}

	var variationForms = doc.querySelectorAll('form.variations_form');

	variationForms.forEach(function (vForm) {
		var selects = vForm.querySelectorAll('table.variations select');
		if (!selects.length) return;

		var waBox = doc.querySelector('.single-product__wa');
		var waLink = waBox ? waBox.querySelector('a[data-wa]') : null;
		var baseName = waBox ? waBox.getAttribute('data-product-name') || '' : '';
		var basePrice = waBox ? waBox.getAttribute('data-product-price') || '' : '';

		function updateSingleWhatsApp(variationData) {
			if (!waLink || !baseName) return;

			var chosen = [];
			selects.forEach(function (sel) {
				if (!sel.value) return;
				var opt = sel.querySelector('option[value="' + CSS.escape(sel.value) + '"]');
				var optLabel = opt ? clean(opt.textContent) : sel.value;

				var row = sel.closest('tr');
				var thLabel = row ? row.querySelector('th.label label') : null;
				var attrTitle = '';
				if (thLabel) {
					var clone = thLabel.cloneNode(true);
					var selSpan = clone.querySelector('.variation-selected-label');
					if (selSpan) selSpan.remove();
					attrTitle = clean(clone.textContent).replace(/:$/, '');
				}
				chosen.push(attrTitle ? attrTitle + ': ' + optLabel : optLabel);
			});

			var currentPrice = basePrice;
			if (variationData && variationData.price_html) {
				var tmp = doc.createElement('div');
				tmp.innerHTML = variationData.price_html;
				var extracted = clean(tmp.textContent);
				if (extracted) currentPrice = extracted;
			} else {
				var varPriceEl = vForm.querySelector('.woocommerce-variation-price .price');
				if (varPriceEl && clean(varPriceEl.textContent)) {
					currentPrice = clean(varPriceEl.textContent);
				}
			}

			var fullTitle = baseName + (chosen.length ? ' — ' + chosen.join(', ') : '');
			var msg =
				'¡Hola! Consulto por el producto "' +
				fullTitle +
				'"' +
				(currentPrice ? ' (' + currentPrice + ')' : '') +
				'. ¿Me contás disponibilidad?';

			var number = (window.libreriatresData && window.libreriatresData.whatsapp) || '';
			if (number) {
				waLink.setAttribute(
					'href',
					'https://wa.me/' + number + '?text=' + encodeURIComponent(msg)
				);
			}
		}

		selects.forEach(function (select) {
			var row = select.closest('tr');
			var labelEl = row ? row.querySelector('th.label label') : null;
			var attrName = labelEl ? clean(labelEl.textContent) : (select.getAttribute('name') || '');

			var selectedSpan = null;
			if (labelEl) {
				selectedSpan = labelEl.querySelector('.variation-selected-label');
				if (!selectedSpan) {
					selectedSpan = doc.createElement('span');
					selectedSpan.className = 'variation-selected-label';
					labelEl.appendChild(selectedSpan);
				}
			}

			// Guardamos todas las opciones iniciales (excepto el placeholder vacio)
			var initialOptions = [];
			Array.prototype.forEach.call(select.options, function (opt) {
				if (!opt.value) return;
				initialOptions.push({
					value: opt.value,
					label: clean(opt.textContent)
				});
			});

			if (!initialOptions.length) return;

			var pillsWrap = doc.createElement('div');
			pillsWrap.className = 'variation-pills';
			pillsWrap.setAttribute('role', 'radiogroup');
			if (attrName) pillsWrap.setAttribute('aria-label', attrName);

			initialOptions.forEach(function (item) {
				var btn = doc.createElement('button');
				btn.type = 'button';
				btn.className = 'variation-pill';
				btn.setAttribute('data-value', item.value);

				var swatch = resolveSwatchColor(attrName, item.label, item.value);
				if (swatch) {
					var dot = doc.createElement('span');
					dot.className = 'variation-pill__dot';
					dot.style.background = swatch;
					btn.appendChild(dot);
				}

				var txt = doc.createElement('span');
				txt.className = 'variation-pill__text';
				txt.textContent = item.label;
				btn.appendChild(txt);

				btn.addEventListener('click', function () {
					if (btn.classList.contains('is-disabled')) return;

					// Si ya estaba activa y la vuelve a tocar, deselecciona; si no, selecciona
					var nextValue = select.value === item.value ? '' : item.value;
					select.value = nextValue;

					if (window.jQuery) {
						window.jQuery(select).trigger('change');
					} else {
						select.dispatchEvent(new Event('change', { bubbles: true }));
					}
					syncPills();
					updateSingleWhatsApp();
				});

				pillsWrap.appendChild(btn);
			});

			select.classList.add('is-swatch-hidden');
			select.parentNode.insertBefore(pillsWrap, select);

			function syncPills() {
				var currentVal = select.value;
				var activeLabel = '';

				pillsWrap.querySelectorAll('.variation-pill').forEach(function (btn) {
					var val = btn.getAttribute('data-value');
					var optInSelect = select.querySelector('option[value="' + CSS.escape(val) + '"]');
					var isAvailable = optInSelect && !optInSelect.disabled;
					var isActive = currentVal === val;

					btn.classList.toggle('is-active', isActive);
					btn.classList.toggle('is-disabled', !isAvailable);
					btn.disabled = !isAvailable;
					btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');

					if (isActive && optInSelect) {
						activeLabel = clean(optInSelect.textContent);
					}
				});

				if (selectedSpan) {
					selectedSpan.textContent = activeLabel ? '— ' + activeLabel : '';
				}
			}

			select.addEventListener('change', function () {
				syncPills();
				updateSingleWhatsApp();
			});

			if (window.jQuery) {
				window.jQuery(vForm).on(
					'woocommerce_update_variation_values reset_data',
					function () {
						setTimeout(function () {
							syncPills();
							updateSingleWhatsApp();
						}, 10);
					}
				);
				window.jQuery(vForm).on('found_variation', function (evt, variation) {
					syncPills();
					updateSingleWhatsApp(variation);
				});
			}

			syncPills();
		});
	});
})();
