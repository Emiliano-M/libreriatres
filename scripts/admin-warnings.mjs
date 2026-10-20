/**
 * Muestra los avisos de PHP que aparezcan en el panel de administracion.
 * Uso: node scripts/admin-warnings.mjs
 */
import { loadConfig } from './hostinger.mjs';

const cfg = loadConfig();

const res = await fetch(
	`https://developers.hostinger.com/api/hosting/v1/accounts/${cfg.username}/wordpress/${cfg.software}/login/links`,
	{
		method: 'POST',
		headers: { Authorization: `Bearer ${cfg.token}`, 'Content-Type': 'application/json' },
	}
);

const payload = await res.json();
const data = payload.data || payload;

const login = await fetch(`https://${data.login || data.fallback}`, { redirect: 'manual' });
const jar = (login.headers.getSetCookie ? login.headers.getSetCookie() : [])
	.map((c) => c.split(';')[0])
	.join('; ');

const admin = await fetch(`https://${cfg.domain}/wp-admin/admin.php?page=libreriatres`, {
	headers: { cookie: jar },
	redirect: 'manual',
});

const html = await admin.text();

const re = /(Warning|Notice|Deprecated|Fatal error)[\s\S]{0,300}/g;
let found = 0;

for (const m of html.matchAll(re)) {
	found++;
	console.log('>>', m[0].replace(/\s+/g, ' ').slice(0, 260));
	if (found > 8) break;
}

console.log(found ? `\n${found} avisos encontrados.` : 'Sin avisos de PHP.');
