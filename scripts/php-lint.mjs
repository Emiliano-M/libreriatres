/**
 * Chequeo de sintaxis PHP: balance de llaves respetando los modos
 * HTML / PHP igual que el lexer de PHP (reemplaza a `php -l`).
 *
 * Uso: node scripts/php-lint.mjs
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.join(path.dirname(fileURLToPath(import.meta.url)), '..');
const DIRS = ['theme', 'plugin'];

const HTML = 0;
const PHP = 1;

function check(file) {
	const src = fs.readFileSync(file, 'utf8');
	const stack = [];
	const pairs = { ')': '(', ']': '[', '}': '{' };
	const errors = [];

	let mode = HTML;
	let line = 1;
	let i = 0;

	const phpOpen = src.startsWith('<?php', i) || src.startsWith('<?=', i);

	while (i < src.length) {
		const c = src[i];
		const next = src[i + 1];

		if (c === '\n') {
			line++;
			i++;
			continue;
		}

		// --- Salida de PHP hacia HTML ---
		if (mode === PHP && c === '?' && next === '>') {
			mode = HTML;
			i += 2;
			continue;
		}

		// --- Entrada de HTML hacia PHP ---
		if (mode === HTML) {
			if (phpOpen) {
				mode = PHP;
				i += src.startsWith('<?php', i) ? 5 : 3;
				continue;
			}
			i++;
			continue;
		}

		// --- Modo PHP ---
		if (c === '/' && next === '/') {
			while (i < src.length && src[i] !== '\n') i++;
			continue;
		}
		if (c === '#' && next !== '[') {
			while (i < src.length && src[i] !== '\n') i++;
			continue;
		}
		if (c === '/' && next === '*') {
			const end = src.indexOf('*/', i + 2);
			if (end === -1) {
				errors.push(`comentario sin cerrar en linea ${line}`);
				break;
			}
			for (let k = i; k < end; k++) if (src[k] === '\n') line++;
			i = end + 2;
			continue;
		}
		if (c === "'" || c === '"') {
			const quote = c;
			const start = line;
			i++;
			while (i < src.length) {
				if (src[i] === '\\') {
					i += 2;
					continue;
				}
				if (src[i] === quote) break;
				if (src[i] === '\n') line++;
				i++;
			}
			if (i >= src.length) {
				errors.push(`cadena sin cerrar en linea ${start}`);
				break;
			}
			i++;
			continue;
		}
		if (src.startsWith('<<<', i)) {
			const m = /^<<<\s*'?([A-Za-z_]+)'?\r?\n/.exec(src.slice(i));
			if (m) {
				const tag = m[1];
				const end = src.indexOf('\n' + tag, i + m[0].length);
				if (end === -1) {
					errors.push(`heredoc sin cerrar en linea ${line}`);
					break;
				}
				for (let k = i; k < end; k++) if (src[k] === '\n') line++;
				i = end + tag.length + 1;
				continue;
			}
		}

		if ('([{'.includes(c)) {
			stack.push({ c, line });
		} else if (')]}'.includes(c)) {
			const top = stack.pop();
			if (!top || top.c !== pairs[c]) {
				errors.push(`"${c}" sin pareja en linea ${line}`);
			}
		}

		i++;
	}

	for (const s of stack) {
		errors.push(`"${s.c}" sin cerrar, abierto en linea ${s.line}`);
	}

	if (mode === HTML && src.trim() !== '' && stack.length === 0 && errors.length === 0) {
		// El archivo es HTML puro: no pasa por el lexer de PHP, esta bien.
	}

	return errors;
}

let failed = 0;
let total = 0;

for (const dir of DIRS) {
	const base = path.join(ROOT, dir);
	if (!fs.existsSync(base)) continue;

	const walk = (d) => {
		for (const e of fs.readdirSync(d, { withFileTypes: true })) {
			const p = path.join(d, e.name);
			if (e.isDirectory()) walk(p);
			else if (e.name.endsWith('.php')) {
				total++;
				const errs = check(p);
				const rel = path.relative(ROOT, p);
				if (errs.length) {
					failed++;
					console.error(`x ${rel}`);
					errs.forEach((x) => console.error(`   ${x}`));
				} else {
					console.log(`ok ${rel}`);
				}
			}
		}
	};

	walk(base);
}

console.log(`\n${total - failed}/${total} archivos OK.`);
process.exit(failed ? 1 : 0);
