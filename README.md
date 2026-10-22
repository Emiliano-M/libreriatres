# Librería Tres — tienda online (WordPress + WooCommerce)

Tienda WooCommerce con diseño a medida para **Librería Tres** (librería, papelería e
impresiones). El sitio vive en Hostinger y se despliega con scripts propios contra la
**API de Hostinger**, sin tocar el panel hPanel.

- **Producción:** https://libreriatres.com
- **Tema:** `theme/libreriatres` — HTML/CSS/JS propio, sin Elementor.
- **Plugin:** `plugin/libreriatres-core` — deja la tienda lista (páginas, menú, categorías,
  productos, ajustes de WooCommerce y datos del negocio).

---

## 1. Estructura

```
.
├── .env                       # API_TOKEN de Hostinger (no versionar)
├── config.json                # usuario, dominio, id de instalación WP, WhatsApp
├── theme/libreriatres/        # tema a medida
│   ├── functions.php          # setup, enqueues, helpers, iconos SVG, tweaks WooCommerce
│   ├── header.php footer.php front-page.php page.php single.php archive.php search.php 404.php
│   ├── woocommerce.php        # wrapper de shop / producto / carrito / checkout
│   ├── woocommerce/content-product.php   # tarjeta de producto
│   ├── template-parts/        # hero, categorías, destacados, instagram, banner WA, trust
│   └── assets/                # css (main.css + fonts.css), js, img (svg), fonts (woff2)
├── plugin/libreriatres-core/  # plugin de arranque y configuración
│   ├── libreriatres-core.php  # bootstrap + hooks
│   ├── inc/class-options.php  # datos del negocio y ajustes de WooCommerce
│   ├── inc/class-cleanup.php  # borra el contenido de la demo anterior
│   ├── inc/class-media.php    # copia las ilustraciones a la biblioteca de medios
│   ├── inc/class-seed.php     # páginas, menú, categorías, productos
│   ├── inc/class-commerce.php # reglas de venta (por ahora: solo WhatsApp)
│   ├── inc/class-admin.php    # panel "Librería Tres" en el admin
│   └── assets/products/*.png  # fotos de producto (van con el plugin)
├── scripts/                   # herramientas de deploy
│   ├── hostinger.mjs          # cliente de la API de Hostinger
│   ├── deploy.mjs             # deploy completo
│   ├── make-assets.mjs        # genera los gráficos del tema y las fotos de producto
│   ├── fetch-fonts.mjs        # descarga y auto-hospeda Playfair Display + Poppins
│   ├── php-lint.mjs           # chequeo de sintaxis PHP (sin php local)
│   ├── check-cart-msg.mjs     # verifica el mensaje que se manda a WhatsApp
│   └── rm-remote.mjs          # borra archivos remotos
├── assets/products/           # originales de las fotos de producto
├── assets/logo-libreria-tres.png  # logo oficial (se copia al tema y al plugin)
└── tools/                     # utilidades de diagnóstico (no se despliegan)
```

### El logo
El logo oficial vive en `assets/logo-libreria-tres.png` (519×519, fondo transparente
fuera del círculo). Se usa en tres lugares:

- **Header y footer**: `theme/libreriatres/assets/img/logo.png`, junto al nombre en
  Playfair (el logo ya lleva el texto, así que el wordmark va como marca).
- **Favicon / icono del sitio**: el plugin lo registra en la biblioteca de medios y lo
  asigna a la opción `site_icon` de WordPress.

Para cambiarlo: reemplazá el PNG en `assets/` y corré
`node scripts/make-assets.mjs` no hace falta; alcanza con copiar el archivo a
`theme/libreriatres/assets/img/logo.png` y `plugin/libreriatres-core/assets/logo.png`,
desplegar y reejecutar el seed.

## 2. Requisitos

- Node.js 18+ (probado con 24).
- `npm install` (solo para `@resvg/resvg-js`, que rasteriza los SVG a PNG).
- El token de la API en `.env` (hPanel → Perfil → API → Crear token).

## 3. Deploy

```bash
node scripts/php-lint.mjs     # siempre antes de desplegar
node scripts/deploy.mjs       # sube tema + plugin, activa, limpia caché
node scripts/deploy.mjs --deactivate   # además desactiva plugins del sitio viejo
```

El deploy:

1. pide una URL de upload TUS (`POST /hosting/v1/files/upload-urls`),
2. sube **cada archivo** a `wp-content/themes/<slug>/…` y `wp-content/plugins/<slug>/…`,
3. activa el tema (`…/wordpress/{id}/themes/activate`) y el plugin (`…/plugins/activate`),
4. limpia la caché del sitio y de LiteSpeed.

> **No usar** los endpoints `…/wordpress/themes/deploy` ni `…/plugins/deploy`: el file
> browser de Hostinger renombra la carpeta a `<slug>-old-<hash>` y el deploy falla.

Después del deploy, el plugin corre el *seed* solo (detecta que la versión del seed no
coincide) y arma la tienda. También podés forzarlo desde **WP Admin → Librería Tres →
Reejecutar configuración** (borra y recrea el contenido de ejemplo).

## 4. Tareas habituales

### Cambiar el número de WhatsApp, teléfono, dirección u horarios
Opción del plugin (más simple) o por API:

```bash
# WP Admin → Librería Tres → Datos del negocio
```

O editando `config.json` + `plugin/libreriatres-core/inc/class-options.php`.

### Poner las fotos reales de los productos
1. Subir las imágenes a la biblioteca de medios desde el admin de WordPress.
2. En cada producto, elegir la imagen destacada.
3. (Opcional) Borrar las ilustraciones de ejemplo: reemplazar los PNG de
   `plugin/libreriatres-core/assets/products/` y volver a correr el seed.

### Cambiar el dominio
En **Ajustes → Generales** de WordPress y en el DNS del dominio apuntando al hosting.
El tema no tiene el dominio hardcodeado (salvo el `site_url` de `config.json`).

### Las fotos de Instagram en la home
La sección "¡Seguinos en Instagram!" de la home puede mostrar las **últimas 4
publicaciones reales**, cada una enlazando a su post en Instagram.

Hay dos fuentes posibles (se usa la primera que esté configurada):

**1. Feed RSS (lo más simple).** Creás una cuenta gratis en
[rss.app](https://rss.app), agregás la fuente de Instagram de `@libreria.tres`,
copiás la URL del feed y la pegás en **WP Admin → Librería Tres → Feed RSS de
Instagram** → "Probar conexión y refrescar feed".

**2. Graph API de Instagram.** Un access token con scope `instagram_basic`
(app de Meta, scope largo). Se usa solo si no hay un feed RSS configurado.

Sin ninguna de las dos, la sección sigue mostrando los 4 placeholders: el sitio
nunca se rompe.

Detalles de la implementación:

- `plugin/libreriatres-core/inc/class-instagram.php` parsea el feed (RSS 2.0 o
  Atom) y extrae de cada item el link del post, la imagen (`media:content`,
  `media:thumbnail`, `enclosure` o el `<img>` del `content:encoded`) y el caption.
- **Las imágenes se copian al hosting** (no hay hotlink a Instagram) y se reusan
  entre refrescos, así que la biblioteca de medios no se llena de duplicados.
- El feed se cachea 6 h y se refresca por cron cada hora. **Después de refrescar
  se purga la caché de LiteSpeed**, si no la home (que está cacheada) seguiría
  mostrando las publicaciones viejas.
- Si la fuente falla, se guarda el error (visible en el admin) y la home sigue
  con lo último cacheado o con los placeholders.

> **Por qué hace falta una de las dos**: Instagram cerró el oEmbed oficial
> (devuelve el muro de login) y el perfil ya no expone los posts en el HTML, ni
> siquiera para crawlers. Lo único que sigue abierto es el `og:image` de un post
> individual, pero eso no alcanza para saber *cuáles* son los últimos.

### MercadoPago / pagos onlineHoy la tienda **no tiene checkout online**: el pedido se arma en el carrito y se
cierra por WhatsApp. Eso lo define `plugin/libreriatres-core/inc/class-commerce.php`:

- `woocommerce_available_payment_gateways` devuelve una lista vacía (no hay pasarela).
- `/checkout/` redirige al carrito.

Para habilitar el pago online (cuando cargues las credenciales de MercadoPago):

1. **WP Admin → Librería Tres** → activar la opción de pagos online
   (`libreriatres_pagos_online` = `yes`), o editarla con:
   ```php
   update_option( 'libreriatres_pagos_online', 'yes' );
   ```
2. **WooCommerce → Ajustes → Pagos** → cargar Access Token y Public Key de MercadoPago.
3. Descomentar el `remove_action` del botón "Proceder al checkout" en
   `theme/libreriatres/functions.php` (`libreriatres_cart_remove_checkout`) y sacar
   las reglas de `display: none` del bloque `.woocommerce .cart .checkout-button`
   en `assets/css/main.css`.

### El carrito y el pedido por WhatsApp
- Cada card de producto tiene **"Consultanos por WhatsApp"** (mensaje con el producto
  y el precio) y **"Agregar al carrito"** debajo.
- Si el producto ya está en el carrito, el botón queda como **"En el carrito"**. Como
  las páginas se cachean (LiteSpeed), ese estado se consulta por AJAX
  (`admin-ajax.php?action=libreriatres_cart_items`).
- En `/carrito/` no hay checkout: hay un bloque **"Cerrá el pedido por WhatsApp"** que
  arma el mensaje con todos los productos, cantidades, subtotales, el total y el link
  del carrito, y abre `wa.me` con todo eso cargado.
- `node scripts/check-cart-msg.mjs` imprime en la terminal ese mismo mensaje, para
  verificar la extracción sin abrir el navegador.
- `node scripts/check-admin.mjs` entra al panel con un link de auto-login y verifica
  que la página de ajustes cargue sin errores de PHP.

### Regenerar los gráficos y las tipografías

```bash
node scripts/make-assets.mjs   # hero, insta-*.svg, favicon, fotos de producto
node scripts/fetch-fonts.mjs   # Playfair Display + Poppins auto-hospedadas
```

## 5. Diseño

| Token | Valor |
|---|---|
| Berry (principal) | `#A81E5B` |
| Rosa | `#F7C8DC` / `#FDEEF3` |
| Verde | `#6FC0A0` |
| Azul | `#A9C7E8` |
| Lila | `#D6BEE8` |
| WhatsApp | `#25D366` |
| Títulos | Playfair Display |
| Texto | Poppins |

Las variables están en `theme/libreriatres/assets/css/main.css` (bloque `:root`).

## 6. Utilidades de diagnóstico (`tools/`)

No se despliegan. Si hace falta subirlos a mano al hosting:

- `tools/lt-check.php` — tokeniza un archivo PHP del sitio y muestra el error de parseo.
- `tools/lt-wp.php` — carga WordPress y permite ver el estado de la tienda, correr el
  seed o ejecutar consultas SQL de solo lectura.
- `tools/lt-debug.php` — mu-plugin que registra errores fatales en `wp-content/lt-error.log`.

## 7. Notas del hosting

- Base de la API: `https://developers.hostinger.com/api` (el `api.hostinger.com` viejo ya no).
- Límite: 90 requests/minuto por token.
- Los `.env` y tokens nunca se suben al hosting: el deploy solo sube `theme/` y `plugin/`.

## 8. Mobile

- El header en mobile es una grilla de 3 columnas: **menú a la izquierda, logo al
  centro, WhatsApp + carrito a la derecha** (queda simétrico).
- El tagline `LIBRERÍA · PAPELERÍA · IMPRESIONES` se oculta en mobile.
- `html` y `body` llevan `overflow-x: clip`: la página **no** tiene scroll horizontal.
  Los "destacados" de la home sí scrollean en horizontal, pero es un carrusel
  propio (`.products-grid--scroll`), no la página.
- Para revisar desbordes: cargar la página con `?debug-overflow` y se muestra un
  informe con los elementos que se salen del viewport y los anchos de
  `html`/`body` (`theme/libreriatres/assets/js/debug-overflow.js`).
