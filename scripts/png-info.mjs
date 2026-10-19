/**
 * Decodifica un PNG (RGBA, 8 bit) y permite samplear pixeles.
 * Uso: node scripts/png-info.mjs <archivo.png> [x,y ...]
 */
import fs from 'node:fs';
import zlib from 'node:zlib';

export function decodePng(file) {
	const buf = fs.readFileSync(file);
	if (buf.readUInt32BE(0) !== 0x89504e47) throw new Error('no es PNG');

	const width = buf.readUInt32BE(16);
	const height = buf.readUInt32BE(20);
	const depth = buf[24];
	const colorType = buf[25];
	if (depth !== 8) throw new Error('solo 8 bit');
	if (colorType !== 6) throw new Error('solo RGBA');

	let i = 8;
	const idat = [];

	while (i < buf.length) {
		const len = buf.readUInt32BE(i);
		const type = buf.toString('ascii', i + 4, i + 8);
		if (type === 'IDAT') idat.push(buf.subarray(i + 8, i + 8 + len));
		i += 12 + len;
		if (type === 'IEND') break;
	}

	const raw = zlib.inflateSync(Buffer.concat(idat));
	const bpp = 4;
	const stride = width * bpp;
	const out = Buffer.alloc(height * stride);

	let pos = 0;
	for (let y = 0; y < height; y++) {
		const filter = raw[pos++];
		const line = raw.subarray(pos, pos + stride);
		pos += stride;

		const cur = out.subarray(y * stride, (y + 1) * stride);
		const prev = y > 0 ? out.subarray((y - 1) * stride, y * stride) : null;

		for (let x = 0; x < stride; x++) {
			const a = x >= bpp ? cur[x - bpp] : 0;
			const b = prev ? prev[x] : 0;
			const c = prev && x >= bpp ? prev[x - bpp] : 0;
			const v = line[x];

			switch (filter) {
				case 0: cur[x] = v; break;
				case 1: cur[x] = (v + a) & 0xff; break;
				case 2: cur[x] = (v + b) & 0xff; break;
				case 3: cur[x] = (v + ((a + b) >> 1)) & 0xff; break;
				case 4: {
					const p = a + b - c;
					const pa = Math.abs(p - a);
					const pb = Math.abs(p - b);
					const pc = Math.abs(p - c);
					const pr = pa <= pb && pa <= pc ? a : pb <= pc ? b : c;
					cur[x] = (v + pr) & 0xff;
					break;
				}
				default: throw new Error('filtro PNG desconocido: ' + filter);
			}
		}
	}

	return {
		width,
		height,
		px(x, y) {
			const o = y * stride + x * bpp;
			return { r: out[o], g: out[o + 1], b: out[o + 2], a: out[o + 3] };
		},
	};
}

if (process.argv[1] && process.argv[1].endsWith('png-info.mjs')) {
	const img = decodePng(process.argv[2]);
	console.log(`${process.argv[2]} -> ${img.width}x${img.height}`);

	const points = [
		[0, 0],
		[img.width - 1, 0],
		[0, img.height - 1],
		[img.width - 1, img.height - 1],
		[Math.round(img.width / 2), Math.round(img.height * 0.08)],
		[Math.round(img.width / 2), Math.round(img.height * 0.5)],
	];

	for (const [x, y] of points) {
		const p = img.px(x, y);
		console.log(`  (${x},${y}) rgba(${p.r},${p.g},${p.b},${p.a})`);
	}

	// Cuantos pixeles son totalmente transparentes.
	let transparent = 0;
	for (let y = 0; y < img.height; y += 2) {
		for (let x = 0; x < img.width; x += 2) {
			if (img.px(x, y).a < 10) transparent++;
		}
	}
	const total = Math.ceil(img.width / 2) * Math.ceil(img.height / 2);
	console.log(`  transparentes: ${((transparent / total) * 100).toFixed(1)}%`);
}
