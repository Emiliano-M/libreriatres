/**
 * Borra un archivo remoto via TUS DELETE.
 * Uso: node scripts/rm-remote.mjs wp-content/themes/libreriatres/__probe.txt
 */
import { loadConfig, getUploadUrl } from './hostinger.mjs';

const cfg = loadConfig();
const targets = process.argv.slice(2);

if (!targets.length) {
	console.error('Indica al menos un archivo a borrar.');
	process.exit(1);
}

const up = await getUploadUrl(cfg.username, cfg.domain);

for (const rel of targets) {
	const url = `${up.url.replace(/\/$/, '')}/${rel}`;
	const res = await fetch(url, {
		method: 'DELETE',
		headers: {
			'X-Auth': up.auth_key,
			'X-Auth-Rest': up.rest_auth_key,
			'Tus-Resumable': '1.0.0',
		},
	});
	console.log(`${rel} -> ${res.status}`);
}
