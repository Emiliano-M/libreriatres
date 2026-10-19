/**
 * Deploy del tema y del plugin a Hostinger via la API.
 *
 * Pasos:
 *  1. Genera una URL de upload TUS.
 *  2. Sube cada archivo del tema a wp-content/themes/<slug>/...
 *     y del plugin a wp-content/plugins/<slug>/...
 *  3. Activa el tema y el plugin.
 *  4. Limpia la cache del sitio y de LiteSpeed.
 *
 * Nota: no usamos los endpoints /wordpress/themes/deploy ni /plugins/deploy
 * porque el file browser de Hostinger renombra la carpeta a "<slug>-old-<hash>"
 * cuando la carpeta destino ya existe. Subimos los archivos y activamos.
 *
 * Uso: node scripts/deploy.mjs [--no-cache] [--deactivate]
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { loadConfig, getUploadUrl, uploadFile, host, walk, api } from './hostinger.mjs';

const ROOT = path.join(path.dirname(fileURLToPath(import.meta.url)), '..');
const cfg = loadConfig();

const THEME_DIR = path.join(ROOT, 'theme', cfg.themeSlug);
const PLUGIN_DIR = path.join(ROOT, 'plugin', cfg.pluginSlug);

if (!cfg.token) {
	console.error('Falta API_TOKEN en .env');
	process.exit(1);
}

/** Plugins del sitio viejo que quedan desactivados. */
const PLUGINS_TO_DISABLE = [
	'elementor',
	'surerank',
	'wpcodebox2',
	'astra-sites',
	'custom-css-for-elementor',
	'header-footer-elementor',
	'hostinger-easy-onboarding',
];

/**
 * Sube todos los archivos de un directorio local a una carpeta remota.
 */
async function uploadTree(upload, localDir, remoteBase) {
	if (!fs.existsSync(localDir)) {
		throw new Error(`No existe el directorio local: ${localDir}`);
	}

	const files = walk(localDir);
	let count = 0;

	for (const file of files) {
		const remote = `${remoteBase}/${file.rel}`;
		await uploadFile(upload, remote, file.abs);
		count++;
		process.stdout.write(`  · ${remote}\n`);
	}

	return count;
}

/**
 * Verifica que la carpeta remota exista y devuelva sus entradas.
 */
async function listRemote(dir) {
	try {
		const res = await host.listFiles(cfg.username, cfg.domain, dir);
		return res.items.filter((i) => !i.path.includes('/'));
	} catch {
		return null;
	}
}

console.log(`\n▸ Deploy en ${cfg.domain}\n`);

const upload = await getUploadUrl(cfg.username, cfg.domain);
console.log('▸ Upload endpoint OK');

console.log('\n▸ Subiendo tema...');
const themeFiles = await uploadTree(upload, THEME_DIR, `wp-content/themes/${cfg.themeSlug}`);
console.log(`  ${themeFiles} archivos del tema subidos`);

console.log('\n▸ Subiendo plugin...');
const pluginFiles = await uploadTree(upload, PLUGIN_DIR, `wp-content/plugins/${cfg.pluginSlug}`);
console.log(`  ${pluginFiles} archivos del plugin subidos`);

// Verifica que las carpetas queden con el nombre correcto.
for (const [label, dir] of [
	['tema', `wp-content/themes/${cfg.themeSlug}`],
	['plugin', `wp-content/plugins/${cfg.pluginSlug}`],
]) {
	const items = await listRemote(dir);
	if (items === null) {
		console.error(`  ! No se pudo leer la carpeta del ${label}: ${dir}`);
	} else {
		console.log(`  · ${label}: ${items.length} entradas en ${dir}`);
	}
}

console.log('\n▸ Activando tema...');
try {
	await api(`/hosting/v1/accounts/${cfg.username}/wordpress/${cfg.software}/themes/activate`, {
		method: 'POST',
		body: { theme: cfg.themeSlug },
	});
	console.log('  tema activado');
} catch (e) {
	// La API devuelve 500 cuando el tema ya esta activo: no es un problema.
	const themes = await api(`/hosting/v1/accounts/${cfg.username}/wordpress/${cfg.software}/themes`).catch(() => null);
	const list = Array.isArray(themes) ? themes : themes?.data || themes?.items || [];
	const activo = list.find((t) => t.status === 'active' || t.active);

	if (activo && String(activo.name || activo.slug || activo.theme).includes(cfg.themeSlug)) {
		console.log(`  tema ya estaba activo (${cfg.themeSlug})`);
	} else {
		console.error('  ERROR al activar el tema:', e.message.split('\n')[0]);
		console.error('  (el plugin lo activa solo en el primer request)');
	}
}

console.log('\n▸ Activando plugin...');
try {
	await api(`/hosting/v1/accounts/${cfg.username}/wordpress/${cfg.software}/plugins/activate`, {
		method: 'POST',
		body: { plugin: cfg.pluginSlug },
	});
	console.log('  plugin activado');
} catch (e) {
	console.error('  ERROR al activar el plugin:', e.message.split('\n')[0]);
}

if (process.argv.includes('--deactivate')) {
	console.log('\n▸ Desactivando plugins del sitio viejo...');
	for (const plugin of PLUGINS_TO_DISABLE) {
		try {
			await api(`/hosting/v1/accounts/${cfg.username}/wordpress/${cfg.software}/plugins/deactivate`, {
				method: 'POST',
				body: { plugin },
			});
			console.log(`  · ${plugin} desactivado`);
		} catch (e) {
			console.log(`  · ${plugin}: ${e.message.split('\n')[0].slice(0, 90)}`);
		}
	}
}

if (!process.argv.includes('--no-cache')) {
	console.log('\n▸ Limpiando cache...');
	try {
		await api(`/hosting/v1/accounts/${cfg.username}/websites/${cfg.domain}/cache/clear`, {
			method: 'DELETE',
		});
		console.log('  cache del sitio limpiada');
	} catch (e) {
		console.error('  cache:', e.message.split('\n')[0]);
	}
	try {
		await api(`/hosting/v1/accounts/${cfg.username}/wordpress/${cfg.software}/litespeed-cache/purge`, {
			method: 'POST',
		});
		console.log('  cache de LiteSpeed limpiada');
	} catch (e) {
		console.error('  litespeed:', e.message.split('\n')[0]);
	}
}

console.log('\n✔ Deploy terminado. Mirá https://' + cfg.domain + '\n');
