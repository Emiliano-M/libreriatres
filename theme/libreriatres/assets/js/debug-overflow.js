/**
 * Diagnostico de scroll horizontal.
 *
 * Se carga solo si la URL trae ?debug-overflow y muestra un informe con los
 * elementos que se salen del viewport (util para revisar el mobile).
 */
(function () {
	'use strict';

	window.addEventListener('load', function () {
		setTimeout(function () {
			var vw = document.documentElement.clientWidth;
			var out = [
				'viewport=' + vw,
				'html.scrollWidth=' + document.documentElement.scrollWidth,
				'body.scrollWidth=' + document.body.scrollWidth,
				'',
			];

			var all = document.querySelectorAll('body *');

			for (var i = 0; i < all.length; i++) {
				var el = all[i];
				var r = el.getBoundingClientRect();

				if (r.right > vw + 1 || r.left < -1) {
					out.push(
						el.tagName.toLowerCase() +
						'.' + String(el.className || '').split(' ').slice(0, 3).join('.') +
						' | left=' + Math.round(r.left) +
						' right=' + Math.round(r.right) +
						' w=' + Math.round(r.width)
					);

					if (out.length > 40) {
						break;
					}
				}
			}

			var box = document.createElement('pre');
			box.id = 'overflow-report';
			box.textContent = out.join('\n');
			box.style.cssText =
				'position:fixed;inset:0;z-index:99999;background:#fff;color:#000;' +
				'font:11px monospace;padding:8px;overflow:auto;white-space:pre-wrap';

			document.body.appendChild(box);
		}, 1200);
	});
})();
