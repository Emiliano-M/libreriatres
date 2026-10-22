/**
 * Cliente mínimo de la API de Hostinger (hPanel API).
 * Base: https://developers.hostinger.com
 * Docs: https://docs.hostinger.com/api-reference/overview
 */
import fs from 'node:fs';
import path from 'node:path';

const API_BASE = 'https://developers.hostinger.com/api';
const ROOT = path.resolve(path.dirname(new URL(import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1')), '..');

/** Lee el .env del proyecto (soporta comillas y comentarios). */
export function loadEnv(file = path.join(ROOT, '.env')) {
  if (!fs.existsSync(file)) return {};
  const out = {};
  for (const raw of fs.readFileSync(file, 'utf8').split(/\r?\n/)) {
    const line = raw.trim();
    if (!line || line.startsWith('#')) continue;
    const i = line.indexOf('=');
    if (i === -1) continue;
    const k = line.slice(0, i).trim();
    let v = line.slice(i + 1).trim();
    if (/^["'].*["']$/.test(v)) v = v.slice(1, -1);
    out[k] = v;
  }
  return out;
}

export function loadConfig() {
  const env = loadEnv();
  const file = path.join(ROOT, 'config.json');
  const cfg = fs.existsSync(file) ? JSON.parse(fs.readFileSync(file, 'utf8')) : {};
  return {
    token: env.API_TOKEN,
    username: cfg.username,
    domain: cfg.domain,
    software: cfg.software,
    ...cfg,
  };
}

export class HostingerError extends Error {
  constructor(status, body, method, url) {
    super(`[${status}] ${method} ${url}\n${typeof body === 'string' ? body : JSON.stringify(body, null, 2)}`);
    this.status = status;
    this.body = body;
  }
}

/** Wrapper de fetch con el token, Content-Type JSON y errores descriptivos. */
export async function api(pathname, { method = 'GET', body, token } = {}) {
  const cfg = loadConfig();
  const tok = token || cfg.token;
  if (!tok) throw new Error('Falta API_TOKEN en .env');

  let res;
  let text;

  for (let attempt = 0; attempt <= 4; attempt++) {
    res = await fetch(API_BASE + pathname, {
      method,
      headers: {
        Authorization: `Bearer ${tok}`,
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: body === undefined ? undefined : JSON.stringify(body),
    });

    text = await res.text();

    // Reintenta ante errores transitorios del backend.
    if (res.status >= 500 && attempt < 4) {
      await new Promise((r) => setTimeout(r, 1200 * (attempt + 1)));
      continue;
    }
    break;
  }
  let data;
  try {
    data = text ? JSON.parse(text) : null;
  } catch {
    data = text;
  }
  if (!res.ok) throw new HostingerError(res.status, data, method, pathname);
  return data;
}
export const host = {
  listWebsites: () => api('/hosting/v1/websites'),
  listWpInstallations: (username, domain) =>
    api(`/hosting/v1/wordpress/installations?username=${username}${domain ? `&domain=${domain}` : ''}`),
  getThemes: (username, software) => api(`/hosting/v1/accounts/${username}/wordpress/${software}/themes`),
  getPlugins: (username, software) => api(`/hosting/v1/accounts/${username}/wordpress/${software}/plugins`),
  activateTheme: (username, software, body) =>
    api(`/hosting/v1/accounts/${username}/wordpress/${software}/themes/activate`, { method: 'POST', body }),
  deployTheme: (username, domain, body) =>
    api(`/hosting/v1/accounts/${username}/websites/${domain}/wordpress/themes/deploy`, { method: 'POST', body }),
  deployPlugin: (username, domain, body) =>
    api(`/hosting/v1/accounts/${username}/websites/${domain}/wordpress/plugins/deploy`, { method: 'POST', body }),
  listFiles: (username, domain, directory = '', extra = {}) => {
    const q = new URLSearchParams({ directory, ...extra });
    return api(`/hosting/v1/accounts/${username}/domains/${domain}/files?${q}`);
  },
  getFileContent: (username, domain, path) =>
    api(`/hosting/v1/accounts/${username}/domains/${domain}/files/content?path=${encodeURIComponent(path)}`),
  clearCache: (username, domain) =>
    api(`/hosting/v1/accounts/${username}/websites/${domain}/cache/clear`, { method: 'DELETE' }),
  purgeLiteSpeed: (username, software) =>
    api(`/hosting/v1/accounts/${username}/wordpress/${software}/litespeed-cache/purge`, { method: 'POST' }),
  updateWpCore: (username, software) =>
    api(`/hosting/v1/accounts/${username}/wordpress/${software}/update`, { method: 'POST' }),
  updatePlugin: (username, software, body) =>
    api(`/hosting/v1/accounts/${username}/wordpress/${software}/plugins/update`, { method: 'POST', body }),
  updateTheme: (username, software, body) =>
    api(`/hosting/v1/accounts/${username}/wordpress/${software}/themes/update`, { method: 'POST', body }),
};

/**
 * Genera una URL de upload TUS con credenciales para subir archivos al
 * document root del sitio (protocolo TUS 1.0.0).
 */
export async function getUploadUrl(username, domain) {
  const res = await api('/hosting/v1/files/upload-urls', { method: 'POST', body: { username, domain } });
  return res.data || res;
}

/**
 * Sube un archivo local a `relativePath` (relativo a public_html) usando TUS.
 */
export async function uploadFile(upload, relativePath, localPath) {
  const buf = fs.readFileSync(localPath);
  const target = `${upload.url.replace(/\/$/, '')}/${relativePath}?override=true`;
  const base = {
    'X-Auth': upload.auth_key,
    'X-Auth-Rest': upload.rest_auth_key,
    'Tus-Resumable': '1.0.0',
  };

  const create = await fetch(target, {
    method: 'POST',
    headers: { ...base, 'Upload-Length': String(buf.length), 'Upload-Offset': '0' },
  });
  if (create.status !== 201) {
    const t = await create.text();
    throw new Error(`TUS create ${relativePath} -> ${create.status}: ${t.slice(0, 300)}`);
  }

  const patch = await fetch(target, {
    method: 'PATCH',
    headers: {
      ...base,
      'Content-Type': 'application/offset+octet-stream',
      'Upload-Offset': '0',
    },
    body: buf,
  });
  if (patch.status !== 204) {
    const t = await patch.text();
    throw new Error(`TUS patch ${relativePath} -> ${patch.status}: ${t.slice(0, 300)}`);
  }
  return { path: relativePath, size: buf.length };
}

/** Lista recursivamente los archivos de un directorio local. */
export function walk(dir, base = dir) {
  const out = [];
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const abs = path.join(dir, entry.name);
    if (entry.isDirectory()) out.push(...walk(abs, base));
    else out.push({ abs, rel: path.relative(base, abs).split(path.sep).join('/') });
  }
  return out;
}
