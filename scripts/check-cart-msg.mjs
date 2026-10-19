/**
 * Verifica que la extraccion del carrito para el mensaje de WhatsApp
 * encuentre los datos correctos (misma logica que assets/js/main.js).
 * Uso: node scripts/check-cart-msg.mjs
 */
const BASE = 'https://forestgreen-coyorte-929041.hostingersite.com'.replace('coyorte', 'coyote');

const jar = [];
const cookie = (res) => {
	const raw = res.headers.getSetCookie ? res.headers.getSetCookie() : [];
	for (const c of raw) {
		jar.push(c.split(';')[0]);
	}
};
const cookieHeader = () => jar.join('; ');

const decode = (t) =>
	(t || '')
		.replace(/<[^>]+>/g, ' ')
		.replace(/&#0?36;/g, '$')
		.replace(/&nbsp;/g, ' ')
		.replace(/&amp;/g, '&')
		.replace(/&[a-z]+;/gi, ' ')
		.replace(/\u00a0/g, ' ')
		.replace(/\s+/g, ' ')
		.trim();

// 1. Productos del shop.
const shop = await (await fetch(`${BASE}/productos/`)).text();
const ids = [...new Set([...shop.matchAll(/name="add-to-cart" value="(\d+)"/g)].map((m) => m[1]))];

// 2. Agregar dos productos al carrito.
for (const id of ids.slice(0, 2)) {
	cookie(await fetch(`${BASE}/?add-to-cart=${id}`, { headers: { cookie: cookieHeader() } }));
}

// 3. Leer el carrito.
const cartRes = await fetch(`${BASE}/carrito/`, { headers: { cookie: cookieHeader() } });
cookie(cartRes);
const cart = await cartRes.text();

// 4. Misma extraccion que el JS del navegador.
const chunks = cart.split('<tr class="woocommerce-cart-form__cart-item').slice(1);
const lines = [];

for (const chunk of chunks) {
	const row = chunk.split('</tr>')[0];

	const name = decode((row.match(/<td[^>]*class="product-name"[^>]*>[\s\S]*?<a[^>]*>([\s\S]*?)<\/a>/) || [])[1]);
	if (!name) continue;

	const qty = ((row.match(/<td[^>]*class="product-quantity"[^>]*>[\s\S]*?<input[^>]*value="([^"]*)"/) || [])[1] || '1').trim();
	const price = decode((row.match(/<td[^>]*class="product-subtotal"[^>]*>([\s\S]*?)<\/td>/) || [])[1]);

	lines.push(`• ${name} x${qty}${price ? ' — ' + price : ''}`);
}

const total = decode(
	(cart.match(/<tr class="order-total">[\s\S]*?<td[^>]*>([\s\S]*?)<\/td>/) || [])[1]
);

console.log('--- mensaje que se manda a WhatsApp ---');
console.log('¡Hola! Quiero hacer este pedido en Librería Tres:\n');
console.log(lines.join('\n'));
if (total) console.log('\nTotal: ' + total);
console.log('\n¿Me confirmás disponibilidad, forma de pago y envío?');

if (!lines.length) {
	console.error('\nFALLO: no se encontraron productos en el carrito.');
	process.exit(1);
}
if (!total) {
	console.error('\nFALLO: no se encontro el total.');
	process.exit(1);
}
