/**
 * Descarga las tipografias del diseno (Playfair Display + Poppins) desde
 * Google Fonts y las deja auto-hospedadas en el tema.
 * Uso: node scripts/fetch-fonts.mjs
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.join(path.dirname(fileURLToPath(import.meta.url)), '..');
const OUT = path.join(ROOT, 'theme', 'libreriatres', 'assets', 'fonts');
fs.mkdirSync(OUT, { recursive: true });

const UA =
  'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36';

const FAMILIES = [
  'Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600',
  'Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400',
];

const url =
  'https://fonts.googleapis.com/css2?' +
  FAMILIES.map((f) => `family=${f}`).join('&') +
  '&display=swap';

const css = await (await fetch(url, { headers: { 'User-Agent': UA } })).text();

const blocks = css.split('@font-face').slice(1);
let out = `/* Tipografias auto-hospedadas. Generado por scripts/fetch-fonts.mjs */\n`;
let count = 0;

for (const b of blocks) {
  const fam = /font-family:\s*'([^']+)'/.exec(b)?.[1];
  const weight = /font-weight:\s*([\d ]+)/.exec(b)?.[1]?.trim();
  const style = /font-style:\s*([a-z]+)/.exec(b)?.[1] || 'normal';
  const src = /url\((https:[^)]+)\)/.exec(b)?.[1];
  const range = /unicode-range:\s*([^;]+);/.exec(b)?.[1]?.trim();
  if (!fam || !src) continue;

  const slug =
    fam.toLowerCase().replace(/\s+/g, '-') +
    '-' +
    weight.replace(/\s+/g, '-') +
    (style === 'italic' ? '-italic' : '') +
    '-' +
    src.split('/').pop();

  const res = await fetch(src, { headers: { 'User-Agent': UA } });
  if (!res.ok) continue;
  const buf = Buffer.from(await res.arrayBuffer());
  fs.writeFileSync(path.join(OUT, slug), buf);
  count++;

  out += `@font-face{font-family:'${fam}';font-style:${style};font-weight:${weight};font-display:swap;src:url(../fonts/${slug}) format('woff2');${range ? `unicode-range:${range};` : ''}}\n`;
  console.log('  ', slug, buf.length, 'bytes');
}

fs.writeFileSync(path.join(ROOT, 'theme', 'libreriatres', 'assets', 'css', 'fonts.css'), out);
console.log(`OK: ${count} archivos de fuente -> theme/libreriatres/assets/fonts/`);
