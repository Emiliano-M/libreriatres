/**
 * Verifica que el panel "Librería Tres" del admin cargue sin errores.
 * Uso: node scripts/check-admin.mjs
 */
import { loadConfig } from './hostinger.mjs';

const cfg = loadConfig();
const BASE = `https://${cfg.domain}`;

const res = await fetch(
	`https://developers.hostinger.com/api/hosting/v1/accounts/${cfg.username}/wordpress/${cfg.software}/login/links`,
	{
		method: 'POST',
		headers: { Authorization: `Bearer ${cfg.token}`, 'Content-Type': 'application/json' },
	}
);

const links = await res.json();
const data = links.data || links;
const login = await fetch(`https://${data.login || data.fallback}`, { redirect: 'manual' });
const jar = (login.headers.getSetCookie ? login.headers.getSetCookie() : []).map((c) => c.split(';')[0]).join('; ');

const admin = await fetch(`${BASE}/wp-admin/admin.php?page=libreriatres`, {
	headers: { cookie: jar },
	redirect: 'manual',
});

const html = await admin.text();
const text = html.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');

console.log('admin status:', admin.status, '| len:', html.length);

const checks = [
	'Librería Tres',
	'Datos del negocio',
	'Pagos online',
	'Reejecutar',
	'WhatsApp (con prefijo',
	'MercadoPago',
	'libreriatres_pagos_online',
	'libreriatres_whatsapp',
	'libreriatres_instagram',
];

for (const c of checks) {
	console.log((html.includes(c) ? 'OK   ' : '--   ') + c);
}

// Busca errores reales de PHP (no los strings de traduccion del admin).
const phpErrors = [
	/<strong>(Warning|Notice|Deprecated|Parse error|Fatal error)<\/strong>:/i,
	/Fatal error:/i,
	/Parse error:/i,
	/Uncaught \w+Error/i,
];

const found = phpErrors.filter((re) => re.test(html));

if (found.length) {
	console.error('FALLO: hay avisos de PHP en el admin.');
	process.exit(1);
}
