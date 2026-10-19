/**
 * Genera los recursos graficos del tema:
 *  - hero.svg, insta-1..4.svg, favicon.svg (SVG nativo)
 *  - assets/products/*.png (ilustraciones planas rasterizadas con resvg)
 *
 * Uso: node scripts/make-assets.mjs
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { Resvg } from '@resvg/resvg-js';

const ROOT = path.join(path.dirname(fileURLToPath(import.meta.url)), '..');
const IMG = path.join(ROOT, 'theme', 'libreriatres', 'assets', 'img');
const PROD = path.join(ROOT, 'assets', 'products');
fs.mkdirSync(IMG, { recursive: true });
fs.mkdirSync(PROD, { recursive: true });

/* ------------------------------------------------------------------ Paleta */
const C = {
	berry: '#a81e5b',
	berrySoft: '#f7c8dc',
	pink50: '#fdf4f6',
	pink100: '#fdeef3',
	pink200: '#f9dfe8',
	green: '#6fc0a0',
	greenSoft: '#dff2ea',
	blue: '#a9c7e8',
	blueSoft: '#e5eef8',
	lilac: '#d6bee8',
	lilacSoft: '#f0e7f7',
	white: '#ffffff',
	ink: '#402f37',
	yellow: '#f7c948',
	orange: '#f2955a',
	red: '#e8636f',
	teal: '#57b6c4',
	grey: '#e6e0e4',
};

/* ------------------------------------------------------------------ Utils */
const svg = (w, h, body) =>
	`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${w} ${h}" width="${w}" height="${h}">${body}</svg>`;

function writeSvg(file, content) {
	fs.writeFileSync(path.join(IMG, file), content);
	console.log('  svg  ', file);
}

function writePng(file, content, size = 900) {
	const resvg = new Resvg(content, {
		fitTo: { mode: 'width', value: size },
		background: 'white',
	});
	const png = resvg.render().asPng();
	fs.writeFileSync(path.join(PROD, file), png);
	console.log('  png  ', file, (png.length / 1024).toFixed(0) + 'kb');
}

/* ============================================================== HERO ==== */
/** Escena de papelería: cuadernos, lápices, mochila, hojas. */
function heroSvg() {
	const pencil = (x, y, rot, color) => `
		<g transform="translate(${x} ${y}) rotate(${rot})">
			<rect x="-9" y="-118" width="18" height="200" rx="4" fill="${color}"/>
			<rect x="-9" y="-118" width="7" height="200" rx="3" fill="#000" opacity=".08"/>
			<path d="M-9 82 L0 104 L9 82 Z" fill="#f3d9b8"/>
			<path d="M-3.5 96 L0 104 L3.5 96 Z" fill="${C.ink}"/>
			<rect x="-9" y="30" width="18" height="12" fill="#cfd6dd"/>
		</g>`;

	return svg(
		640,
		560,
		`<rect width="640" height="560" fill="${C.pink50}"/>
	<circle cx="470" cy="120" r="86" fill="${C.pink200}"/>
	<circle cx="120" cy="450" r="70" fill="${C.greenSoft}"/>
	<circle cx="560" cy="430" r="46" fill="${C.blueSoft}"/>

	<!-- cuaderno rosa -->
	<g transform="translate(120 300) rotate(-9)">
		<rect x="-8" y="-140" width="230" height="290" rx="14" fill="#ffffff"/>
		<rect x="0" y="-150" width="230" height="290" rx="14" fill="${C.berrySoft}"/>
		<rect x="0" y="-150" width="230" height="60" rx="14" fill="${C.berry}" opacity=".16"/>
		<g fill="none" stroke="${C.white}" stroke-width="7" stroke-linecap="round">
			<path d="M-14 -108 h26"/><path d="M-14 -76 h26"/><path d="M-14 -44 h26"/>
			<path d="M-14 -12 h26"/><path d="M-14 20 h26"/><path d="M-14 52 h26"/>
			<path d="M-14 84 h26"/><path d="M-14 116 h26"/>
		</g>
	</g>

	<!-- cuaderno verde -->
	<g transform="translate(320 330) rotate(7)">
		<rect x="-8" y="-120" width="200" height="250" rx="12" fill="#ffffff"/>
		<rect x="0" y="-128" width="200" height="250" rx="12" fill="${C.green}"/>
		<g fill="none" stroke="#ffffff" stroke-width="7" stroke-linecap="round">
			<path d="M-14 -92 h24"/><path d="M-14 -60 h24"/><path d="M-14 -28 h24"/>
			<path d="M-14 4 h24"/><path d="M-14 36 h24"/><path d="M-14 68 h24"/><path d="M-14 100 h24"/>
		</g>
	</g>

	<!-- hojas sueltas -->
	<g transform="translate(250 430) rotate(-6)">
		<rect x="-70" y="-26" width="140" height="96" rx="8" fill="#ffffff" stroke="${C.grey}" stroke-width="3"/>
		<g stroke="${C.blue}" stroke-width="4" stroke-linecap="round" opacity=".7">
			<path d="M-48 4 h96"/><path d="M-48 22 h96"/><path d="M-48 40 h60"/>
		</g>
	</g>

	${pencil(455, 210, 12, C.berry)}
	${pencil(492, 215, 17, C.yellow)}
	${pencil(528, 220, 22, C.teal)}
	${pencil(418, 205, 7, C.orange)}

	<!-- marcadores -->
	<g transform="translate(90 420) rotate(-16)">
		<rect x="-16" y="-70" width="32" height="120" rx="12" fill="${C.blue}"/>
		<rect x="-16" y="-92" width="32" height="30" rx="10" fill="${C.berry}"/>
		<rect x="-16" y="-62" width="32" height="16" fill="#ffffff" opacity=".55"/>
	</g>
	<g transform="translate(140 430) rotate(-9)">
		<rect x="-16" y="-70" width="32" height="120" rx="12" fill="${C.lilac}"/>
		<rect x="-16" y="-92" width="32" height="30" rx="10" fill="${C.green}"/>
		<rect x="-16" y="-62" width="32" height="16" fill="#ffffff" opacity=".55"/>
	</g>

	<!-- hearts -->
	<path d="M545 205 c-8-10-24-4-24 9 0 13 16 24 24 31 8-7 24-18 24-31 0-13-16-19-24-9z" fill="${C.berry}" opacity=".85"/>
	<path d="M70 200 c-6-8-19-3-19 7 0 10 13 19 19 24 6-5 19-14 19-24 0-10-13-15-19-7z" fill="${C.berry}" opacity=".5"/>`
	);
}

/* ========================================================= INSTAGRAM ==== */
function instaSvg(i) {
	const variants = [
		{ bg: C.pink100, a: C.berrySoft, b: C.green, c: C.blue },
		{ bg: C.greenSoft, a: C.green, b: C.yellow, c: C.berry },
		{ bg: C.blueSoft, a: C.blue, b: C.lilac, c: C.berrySoft },
		{ bg: C.lilacSoft, a: C.lilac, b: C.pink200, c: C.teal },
	];
	const v = variants[(i - 1) % variants.length];

	const body = `
	<rect width="200" height="260" fill="${v.bg}"/>
	<rect x="26" y="30" width="148" height="200" rx="16" fill="#ffffff" opacity=".92"/>
	<rect x="38" y="44" width="124" height="70" rx="10" fill="${v.a}"/>
	<rect x="38" y="124" width="56" height="90" rx="10" fill="${v.b}"/>
	<rect x="102" y="124" width="60" height="44" rx="10" fill="${v.c}"/>
	<rect x="102" y="176" width="60" height="38" rx="10" fill="${v.a}" opacity=".65"/>
	<path d="M100 232 c-7-9-21-3-21 8 0 11 14 20 21 26 7-6 21-15 21-26 0-11-14-17-21-8z" fill="${C.berry}"/>`;

	return svg(200, 260, body);
}

/* =========================================================== FAVICON ==== */
function faviconSvg() {
	return svg(
		64,
		64,
		`<rect width="64" height="64" rx="14" fill="${C.pink100}"/>
	<g fill="none" stroke="${C.berry}" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round">
		<path d="M32 30c-3-9-9-15-15-15S4 24 9 32c4 6 12 8 20 6"/>
		<path d="M32 30c3-9 9-15 15-15s13 9 8 17c-4 6-12 8-20 6"/>
		<path d="M32 30c-3 9-8 14-14 14-5 0-8-3-8-7 0-5 4-9 10-10" opacity=".85"/>
		<path d="M32 30c3 9 8 14 14 14 5 0 8-3 8-7 0-5-4-9-10-10" opacity=".85"/>
		<path d="M32 28v18"/>
	</g>`
	);
}

/* ========================================================== PRODUCTOS === */
const bgFor = ['#fdeef3', '#dff2ea', '#e5eef8', '#f0e7f7', '#fdf4f6', '#f9dfe8'];

function productFrame(inner, bg) {
	return svg(
		900,
		900,
		`<rect width="900" height="900" fill="${bg}"/>
	<circle cx="700" cy="190" r="120" fill="#ffffff" opacity=".45"/>
	<circle cx="160" cy="740" r="90" fill="#ffffff" opacity=".35"/>
	<g transform="translate(450 460)">${inner}</g>`
	);
}

const products = {
	/* Cuaderno espiral A4 */
	'cuaderno-espiral-a4': () =>
		productFrame(
			`<g transform="rotate(-6)">
	<rect x="-190" y="-235" width="390" height="470" rx="18" fill="#ffffff"/>
	<rect x="-200" y="-245" width="390" height="470" rx="18" fill="${C.berrySoft}"/>
	<rect x="-200" y="-245" width="390" height="96" rx="18" fill="${C.berry}" opacity=".14"/>
	<g fill="none" stroke="#ffffff" stroke-width="11" stroke-linecap="round">
		<path d="M-262 -176 h36"/><path d="M-262 -122 h36"/><path d="M-262 -68 h36"/>
		<path d="M-262 -14 h36"/><path d="M-262 40 h36"/><path d="M-262 94 h36"/>
		<path d="M-262 148 h36"/><path d="M-262 202 h36"/>
	</g>
	<rect x="-150" y="-130" width="250" height="16" rx="8" fill="${C.berry}" opacity=".35"/>
	<rect x="-150" y="-92" width="190" height="16" rx="8" fill="${C.berry}" opacity=".22"/>
</g>`,
			bgFor[0]
		),

	/* Carpeta N3 */
	'carpeta-n3': () =>
		productFrame(
			`<g transform="rotate(4)">
	<path d="M-230 -180 h150 l44 -54 h266 a26 26 0 0 1 26 26 v384 a26 26 0 0 1 -26 26 h-460 a26 26 0 0 1 -26 -26 v-330 a26 26 0 0 1 26 -26z" fill="${C.blue}"/>
	<path d="M-230 -180 h150 l44 -54 h266 a26 26 0 0 1 26 26 v40 h-486 v-12 a26 26 0 0 1 26 -26z" fill="${C.teal}"/>
	<rect x="-190" y="-60" width="330" height="20" rx="10" fill="#ffffff" opacity=".7"/>
	<rect x="-190" y="-16" width="240" height="20" rx="10" fill="#ffffff" opacity=".45"/>
	<circle cx="120" cy="120" r="42" fill="#ffffff" opacity=".55"/>
	<path d="M100 120 l14 14 28 -30" fill="none" stroke="#ffffff" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
</g>`,
			bgFor[2]
		),

	/* Lapices de colores */
	'lapices-de-colores': () => {
		const cols = [C.berry, C.yellow, C.green, C.teal, C.orange, C.lilac];
		const pens = cols
			.map(
				(c, i) => `<g transform="translate(${-190 + i * 76} 0) rotate(${-8 + i * 3.2})">
	<rect x="-21" y="-260" width="42" height="430" rx="8" fill="${c}"/>
	<rect x="-21" y="-260" width="15" height="430" rx="7" fill="#000000" opacity=".08"/>
	<path d="M-21 170 L0 224 L21 170 Z" fill="#f3d9b8"/>
	<path d="M-8 199 L0 224 L8 199 Z" fill="${C.ink}"/>
	<rect x="-21" y="80" width="42" height="26" fill="#cfd6dd"/>
</g>`
			)
			.join('');
		return productFrame(pens, bgFor[4]);
	},

	/* Fibras x10 */
	'fibras-x10': () => {
		const caps = [C.berry, C.teal, C.green, C.orange, C.lilac, C.blue];
		const pens = caps
			.map(
				(c, i) => `<g transform="translate(${-200 + i * 80} 0) rotate(${-6 + i * 2.4})">
	<rect x="-24" y="-250" width="48" height="420" rx="18" fill="${c}"/>
	<rect x="-24" y="-250" width="48" height="120" rx="16" fill="#ffffff" opacity=".75"/>
	<rect x="-27" y="-292" width="54" height="56" rx="16" fill="${C.ink}" opacity=".85"/>
	<rect x="-24" y="-130" width="48" height="16" fill="#ffffff" opacity=".5"/>
</g>`
			)
			.join('');
		return productFrame(pens, bgFor[1]);
	},

	/* Resaltadores x6 */
	'resaltadores-x6': () => {
		const cols = [C.yellow, C.green, C.blue, C.berry];
		const pens = cols
			.map(
				(c, i) => `<g transform="translate(${-150 + i * 100} 0) rotate(${-5 + i * 3.2})">
	<rect x="-32" y="-230" width="64" height="380" rx="22" fill="${c}"/>
	<rect x="-32" y="-230" width="64" height="110" rx="20" fill="#ffffff" opacity=".7"/>
	<path d="M-32 -230 h64 v-40 a32 32 0 0 0 -64 0z" fill="#ffffff" opacity=".85"/>
	<path d="M-14 -262 l-18 34 h36z" fill="${C.ink}" opacity=".35"/>
</g>`
			)
			.join('');
		return productFrame(pens, bgFor[3]);
	},

	/* Mochila escolar */
	'mochila-escolar': () =>
		productFrame(
			`<g transform="rotate(3)">
	<path d="M-150 -230 a60 60 0 0 1 120 0" fill="none" stroke="${C.berry}" stroke-width="22" stroke-linecap="round"/>
	<path d="M-190 -210 h380 a52 52 0 0 1 52 52 v300 a52 52 0 0 1 -52 52 h-380 a52 52 0 0 1 -52 -52 v-300 a52 52 0 0 1 52 -52z" fill="${C.blue}"/>
	<path d="M-190 -210 h380 a52 52 0 0 1 52 52 v60 h-484 v-60 a52 52 0 0 1 52 -52z" fill="${C.teal}" opacity=".35"/>
	<rect x="-120" y="10" width="240" height="140" rx="26" fill="${C.blueSoft}"/>
	<rect x="-120" y="10" width="240" height="46" rx="22" fill="#ffffff" opacity=".55"/>
	<rect x="-26" y="-120" width="52" height="26" rx="13" fill="${C.berry}"/>
	<path d="M60 -120 c0 44 -28 62 -28 100 0 24 16 40 40 40" fill="none" stroke="${C.white}" stroke-width="10" stroke-linecap="round" opacity=".7"/>
</g>`,
			bgFor[2]
		),

	/* Cuaderno espiral A5 */
	'cuaderno-espiral-a5': () =>
		productFrame(
			`<g transform="rotate(5)">
	<rect x="-160" y="-200" width="320" height="400" rx="16" fill="#ffffff"/>
	<rect x="-170" y="-210" width="320" height="400" rx="16" fill="${C.green}"/>
	<g fill="none" stroke="#ffffff" stroke-width="9" stroke-linecap="round">
		<path d="M-228 -152 h32"/><path d="M-228 -106 h32"/><path d="M-228 -60 h32"/>
		<path d="M-228 -14 h32"/><path d="M-228 32 h32"/><path d="M-228 78 h32"/><path d="M-228 124 h32"/>
	</g>
	<circle cx="-10" cy="-60" r="62" fill="#ffffff" opacity=".32"/>
	<path d="M-10 -96 c-24-30-64-12-64 26 0 38 44 70 64 90 20-20 64-52 64-90 0-38-40-56-64-26z" fill="#ffffff" opacity=".55"/>
</g>`,
			bgFor[1]
		),

	/* Resma de hojas A4 */
	'resma-hojas-a4': () =>
		productFrame(
			`<g transform="rotate(-4)">
	<rect x="-200" y="-230" width="400" height="440" rx="10" fill="#e9e4e7"/>
	<rect x="-192" y="-238" width="400" height="440" rx="10" fill="#f7f5f6"/>
	<rect x="-184" y="-246" width="400" height="440" rx="10" fill="#ffffff"/>
	<g stroke="${C.blue}" stroke-width="9" stroke-linecap="round" opacity=".55">
		<path d="M-130 -180 h260"/><path d="M-130 -140 h260"/><path d="M-130 -100 h260"/>
		<path d="M-130 -60 h260"/><path d="M-130 -20 h260"/><path d="M-130 20 h260"/>
		<path d="M-130 60 h260"/><path d="M-130 100 h180"/>
	</g>
	<rect x="-184" y="-90" width="400" height="86" fill="${C.berrySoft}" opacity=".9"/>
	<rect x="-184" y="-90" width="400" height="86" fill="#ffffff" opacity=".25"/>
	<text x="16" y="-34" font-family="Poppins, sans-serif" font-size="42" font-weight="600" fill="${C.berry}" text-anchor="middle">A4 · 75g</text>
</g>`,
			bgFor[5]
		),

	/* Set de resaltadores */
	'set-resaltadores': () =>
		productFrame(
			`<g transform="rotate(-3)">
	<rect x="-190" y="-120" width="380" height="300" rx="24" fill="${C.lilacSoft}" stroke="${C.lilac}" stroke-width="8"/>
	<rect x="-190" y="-120" width="380" height="90" rx="24" fill="${C.lilac}" opacity=".55"/>
	<g>
		<g transform="translate(-110 -30) rotate(-8)">
			<rect x="-30" y="-150" width="60" height="300" rx="20" fill="${C.yellow}"/>
			<path d="M-30 -150 h60 v-34 a30 30 0 0 0 -60 0z" fill="#ffffff" opacity=".8"/>
		</g>
		<g transform="translate(-10 -30) rotate(-2)">
			<rect x="-30" y="-150" width="60" height="300" rx="20" fill="${C.green}"/>
			<path d="M-30 -150 h60 v-34 a30 30 0 0 0 -60 0z" fill="#ffffff" opacity=".8"/>
		</g>
		<g transform="translate(90 -30) rotate(4)">
			<rect x="-30" y="-150" width="60" height="300" rx="20" fill="${C.blue}"/>
			<path d="M-30 -150 h60 v-34 a30 30 0 0 0 -60 0z" fill="#ffffff" opacity=".8"/>
		</g>
		<g transform="translate(10 20) rotate(8)">
			<rect x="-30" y="-110" width="60" height="250" rx="20" fill="${C.berry}"/>
			<path d="M-30 -110 h60 v-30 a30 30 0 0 0 -60 0z" fill="#ffffff" opacity=".8"/>
		</g>
	</g>
</g>`,
			bgFor[3]
		),

	/* Papel contact tiza */
	'papel-contact-tiza': () =>
		productFrame(
			`<g transform="rotate(-6)">
	<rect x="-230" y="-150" width="460" height="300" rx="26" fill="${C.berrySoft}"/>
	<rect x="-230" y="-150" width="460" height="300" rx="26" fill="url(#none)"/>
	<ellipse cx="230" cy="0" rx="42" ry="150" fill="${C.pink200}"/>
	<ellipse cx="230" cy="0" rx="26" ry="150" fill="#ffffff"/>
	<ellipse cx="230" cy="0" rx="12" ry="150" fill="${C.grey}"/>
	<path d="M-230 -60 a30 30 0 0 1 30 -30 h400" fill="none" stroke="#ffffff" stroke-width="10" opacity=".65"/>
	<text x="-20" y="20" font-family="Playfair Display, serif" font-style="italic" font-size="52" fill="#ffffff" text-anchor="middle">Contact</text>
</g>`,
			bgFor[5]
		),
};

/* =============================================================== Run ==== */
console.log('Generando recursos del tema...');
writeSvg('hero.svg', heroSvg());
for (let i = 1; i <= 4; i++) writeSvg(`insta-${i}.svg`, instaSvg(i));
writeSvg('favicon.svg', faviconSvg());

console.log('Generando fotos de producto...');
for (const [name, fn] of Object.entries(products)) {
	writePng(`${name}.png`, fn());
}

console.log('Listo.');
