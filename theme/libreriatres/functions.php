<?php
/*
Theme Name: Libreria Tres
Theme URI: https://libreriatres.com
Author: Libreria Tres
Description: Tema a medida para Libreria Tres - Libreria, papeleria e impresiones. WooCommerce con pedidos por WhatsApp.
Version: 1.0.0
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: libreriatres
Tags: e-commerce, two-columns, custom-menu, custom-logo, featured-images, translation-ready
WC requires at least: 8.0
WC tested up to: 10.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LIBRERIA_TRES_VERSION', '1.0.0' );
define( 'LIBRERIA_TRES_DIR', get_template_directory() );
define( 'LIBRERIA_TRES_URI', get_template_directory_uri() );

/* -------------------------------------------------------------------------
 * Setup
 * ---------------------------------------------------------------------- */

function libreriatres_setup() {
	load_theme_textdomain( 'libreriatres', LIBRERIA_TRES_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support( 'custom-logo', array( 'height' => 90, 'width' => 260, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'woocommerce', array( 'thumbnail_image_width' => 600 ) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'Menu principal', 'libreriatres' ),
			'footer'  => __( 'Menu de pie', 'libreriatres' ),
		)
	);

	add_image_size( 'libreriatres-card', 600, 600, true );
	add_image_size( 'libreriatres-wide', 1200, 640, true );
}
add_action( 'after_setup_theme', 'libreriatres_setup' );

/**
 * Ancho del contenido segun la plantilla.
 */
function libreriatres_content_width() {
	$GLOBALS['content_width'] = 1200;
}
add_action( 'after_setup_theme', 'libreriatres_content_width', 0 );

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */

function libreriatres_assets() {
	$ver = LIBRERIA_TRES_VERSION;

	// Version por fecha de modificacion: el navegador refresca solo los archivos que cambian.
	$stamp = function ( $relative ) {
		$file = LIBRERIA_TRES_DIR . $relative;
		return file_exists( $file ) ? (string) filemtime( $file ) : LIBRERIA_TRES_VERSION;
	};

	wp_enqueue_style( 'libreriatres-fonts', LIBRERIA_TRES_URI . '/assets/css/fonts.css', array(), $stamp( '/assets/css/fonts.css' ) );
	wp_enqueue_style( 'libreriatres-style', get_stylesheet_uri(), array( 'libreriatres-fonts' ), $stamp( '/style.css' ) );
	wp_enqueue_style( 'libreriatres-main', LIBRERIA_TRES_URI . '/assets/css/main.css', array( 'libreriatres-style' ), $stamp( '/assets/css/main.css' ) );

	wp_enqueue_script( 'libreriatres-main', LIBRERIA_TRES_URI . '/assets/js/main.js', array(), $stamp( '/assets/js/main.js' ), true );

	// Diagnostico de scroll horizontal: ?debug-overflow
	if ( isset( $_GET['debug-overflow'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_enqueue_script(
			'libreriatres-debug-overflow',
			LIBRERIA_TRES_URI . '/assets/js/debug-overflow.js',
			array(),
			$stamp( '/assets/js/debug-overflow.js' ),
			true
		);
	}
	wp_localize_script(
		'libreriatres-main',
		'libreriatresData',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'cartUrl'    => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ),
			'whatsapp'   => libreriatres_whatsapp_number(),
			'cartItems'  => admin_url( 'admin-ajax.php' ) . '?action=libreriatres_cart_items&t=' . time(),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'libreriatres_assets' );

/**
 * Preconnect / dns-prefetch para el dominio de assets.
 */
function libreriatres_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'libreriatres_resource_hints', 10, 2 );

/* -------------------------------------------------------------------------
 * Datos del negocio (opciones que define el plugin libreriatres-core)
 * ---------------------------------------------------------------------- */

/**
 * Numero de WhatsApp en formato internacional, solo digitos.
 */
function libreriatres_whatsapp_number() {
	$number = (string) get_option( 'libreriatres_whatsapp', '5491178195270' );
	return preg_replace( '/\D+/', '', $number );
}

/**
 * Numero de WhatsApp visible para las personas.
 */
function libreriatres_phone_display() {
	$phone = (string) get_option( 'libreriatres_phone', '11 7819 5270' );
	return $phone;
}

/**
 * Direccion y horarios.
 */
function libreriatres_address() {
	return (string) get_option( 'libreriatres_address', '9 de Abril, Esteban Echeverría' );
}

function libreriatres_hours() {
	return (string) get_option( 'libreriatres_hours', 'Lunes a Sábados 14 a 22 hs (verano con cita)' );
}

function libreriatres_instagram() {
	return (string) get_option( 'libreriatres_instagram', 'libreria.tres' );
}

/**
 * Arma un link de WhatsApp con mensaje precargado.
 *
 * @param string $message Texto del mensaje.
 * @param bool   $new_tab Abrir en pestana nueva.
 */
function libreriatres_whatsapp_link( $message = '', $new_tab = true ) {
	$number = libreriatres_whatsapp_number();
	$url    = 'https://wa.me/' . $number;

	if ( $message ) {
		$url = add_query_arg( 'text', rawurlencode( $message ), $url );
	}

	return $new_tab
		? '<a class="btn btn--whatsapp" href="' . esc_url( $url ) . '" target="_blank" rel="noopener" data-wa="1"><span class="btn__icon" aria-hidden="true">'
			. libreriatres_icon( 'whatsapp' ) . '</span><span class="btn__label">' . esc_html__( 'Consultanos por WhatsApp', 'libreriatres' ) . '</span></a>'
		: '<a class="btn btn--whatsapp" href="' . esc_url( $url ) . '" data-wa="1"><span class="btn__icon" aria-hidden="true">'
			. libreriatres_icon( 'whatsapp' ) . '</span><span class="btn__label">' . esc_html__( 'Consultanos por WhatsApp', 'libreriatres' ) . '</span></a>';
}

/**
 * Link de WhatsApp simple (sin boton), para el header y el footer.
 */
function libreriatres_whatsapp_icon_link( $label = '' ) {
	$url = 'https://wa.me/' . libreriatres_whatsapp_number();
	return '<a class="icon-link" href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="WhatsApp">'
		. libreriatres_icon( 'whatsapp' ) . '</a>';
}

/**
 * Link de Instagram.
 */
function libreriatres_instagram_link() {
	$user = libreriatres_instagram();
	return 'https://www.instagram.com/' . ltrim( $user, '@' ) . '/';
}

/* -------------------------------------------------------------------------
 * Iconos (SVG inline, estilo "trazo" como en el diseno)
 * ---------------------------------------------------------------------- */

/**
 * Devuelve un icono SVG inline.
 *
 * @param string $name  Nombre del icono.
 * @param int    $size  Tamano en px.
 */
function libreriatres_icon( $name, $size = 22 ) {
	$paths = array(
		'whatsapp'  => '<path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.86 9.86 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.15h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.11.82.83-3.04-.2-.31a8.17 8.17 0 0 1-1.25-4.38c0-4.54 3.7-8.24 8.23-8.24a8.18 8.18 0 0 1 5.82 2.42 8.16 8.16 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.16.24-.64.8-.78.97-.15.16-.29.19-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.38-1.72-.15-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.15.16-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.35-.77-1.85-.2-.48-.4-.42-.56-.43h-.47c-.17 0-.43.06-.66.31s-.86.85-.86 2.07.89 2.39 1.01 2.56c.12.17 1.74 2.65 4.21 3.71.59.26 1.05.41 1.4.52.59.19 1.12.16 1.55.1.47-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.22-.17-.47-.29Z"/>',
		'user'      => '<path d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0 2c-3.9 0-7 2.2-7 4.9V21h14v-2.1c0-2.7-3.1-4.9-7-4.9Z"/>',
		'cart'      => '<path d="M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM7.2 14h9.9c.8 0 1.5-.45 1.8-1.2l2.5-6.4-1.9-.7-2.4 6.1H7.6L5.4 5.5H2.5v1.6h1.9l2.8 6.9Z"/>',
		'search'    => '<path d="M10.5 3a7.5 7.5 0 1 0 4.55 13.46l4.24 4.25 1.42-1.42-4.25-4.24A7.5 7.5 0 0 0 10.5 3Zm0 2a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Z"/>',
		'heart'     => '<path d="M12 20.7 4.7 13.4a4.6 4.6 0 0 1 6.5-6.5l.8.8.8-.8a4.6 4.6 0 0 1 6.5 6.5Z"/>',
		'menu'      => '<path d="M3 6h18v2H3V6Zm0 5h18v2H3v-2Zm0 5h18v2H3v-2Z"/>',
		'close'     => '<path d="m12 10.6 5-5 1.4 1.4-5 5 5 5-1.4 1.4-5-5-5 5-1.4-1.4 5-5-5-5L6.4 5.6Z"/>',
		'pin'       => '<path d="M12 2a7 7 0 0 0-7 7c0 5.1 6.2 12.4 6.5 12.7a.7.7 0 0 0 1 0C12.8 21.4 19 14.1 19 9a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/>',
		'clock'     => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 5.5V12l3.5 2.1-1 1.7L11 13.2V7.5Z"/>',
		'truck'     => '<path d="M3 5h11v9H3V5Zm12 3h3.5L21 11v3h-6V8ZM6.5 17a1.8 1.8 0 1 0 0 3.6A1.8 1.8 0 0 0 6.5 17Zm11 0a1.8 1.8 0 1 0 0 3.6 1.8 1.8 0 0 0 0-3.6Z"/>',
		'shield'    => '<path d="M12 2 4 5.5V11c0 5 3.4 9.6 8 11 4.6-1.4 8-6 8-11V5.5L12 2Zm-1 13.6-3.3-3.3 1.4-1.4 1.9 1.9 4.3-4.3 1.4 1.4-5.7 5.7Z"/>',
		'trash'     => '<path d="M9 3h6l1 2h4v2H4V5h4l1-2Zm-3 6h12l-1 11.2A2 2 0 0 1 15 22H9a2 2 0 0 1-2-1.8L6 9Z"/>',
		'notebook'  => '<path d="M6 2h11a2 2 0 0 1 2 2v18H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm1 3v2h8V5H7Zm0 4v2h8V9H7Zm0 4v2h5v-2H7Z"/>',
		'file'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm0 2.5L17.5 8H14V4.5ZM8 12h8v2H8v-2Zm0 4h8v2H8v-2Z"/>',
		'printer'   => '<path d="M7 3h10v4H7V3Zm-2 6h14a2 2 0 0 1 2 2v5h-4v5H7v-5H3v-5a2 2 0 0 1 2-2Zm6 7v3h6v-3h-6Z"/>',
		'gift'      => '<path d="M12 2a3 3 0 0 1 3 3c0 .6-.18 1.15-.47 1.6H21v3h-9v13h-2v-13H3v-3h6.47A3 3 0 0 1 12 2Zm0 2a1 1 0 0 0-1 1c0 .4.2.7.5.9H13c.3-.2.5-.5.5-.9a1 1 0 0 0-1-1Z"/>',
		'chat'      => '<path d="M12 3c5 0 9 3.4 9 7.6 0 4.2-4 7.6-9 7.6-.9 0-1.8-.1-2.6-.3L4 20l1.2-3.6C3.2 15 2 12.9 2 10.6 2 6.4 6 3 12 3Z"/>',
		'instagram' => '<path d="M12 2.2c3.2 0 3.6 0 4.9.07 1.2.05 1.8.25 2.2.42.6.22 1 .48 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c0 1.2-.2 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2 0-1.8-.2-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c0-1.2.2-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4 1.3-.1 1.7-.1 4.8-.1Zm0 1.8c-3.1 0-3.5 0-4.7.07-1.1.05-1.7.24-2.1.4-.5.2-.9.44-1.3.83-.4.4-.63.77-.83 1.28-.16.4-.35.98-.4 2.08C2.6 10.4 2.6 10.8 2.6 12s0 1.6.07 2.8c.05 1.1.24 1.68.4 2.08.2.5.44.88.83 1.28.4.4.8.63 1.3.83.4.16.98.35 2.08.4 1.2.07 1.6.07 4.7.07s3.5 0 4.7-.07c1.1-.05 1.68-.24 2.08-.4.5-.2.88-.44 1.28-.83.4-.4.63-.8.83-1.28.16-.4.35-.98.4-2.08.07-1.2.07-1.6.07-2.8s0-1.6-.07-2.8c-.05-1.1-.24-1.68-.4-2.08a3.3 3.3 0 0 0-.83-1.28 3.4 3.4 0 0 0-1.28-.83c-.4-.16-.98-.35-2.08-.4-1.2-.07-1.6-.07-4.7-.07Zm0 3.1a4.9 4.9 0 1 1 0 9.8 4.9 4.9 0 0 1 0-9.8Zm0 1.8a3.1 3.1 0 1 0 0 6.2 3.1 3.1 0 0 0 0-6.2Zm5.1-3.2a1.15 1.15 0 1 1 0 2.3 1.15 1.15 0 0 1 0-2.3Z"/>',
		'arrow'     => '<path d="M13.2 5.6 21 12l-7.8 6.4-1.4-1.7 4.8-3.9H3v-1.6h13.6l-4.8-3.9Z"/>',
		'check'     => '<path d="m9.6 16.2-3.8-3.8-1.4 1.4 5.2 5.2L20 8.6 18.6 7.2Z"/>',
		'plus'      => '<path d="M11 4h2v7h7v2h-7v7h-2v-7H4v-2h7V4Z"/>',
		'minus'     => '<path d="M4 11h16v2H4z"/>',
		'instagram2' => '<path d="M12 2.2c3.2 0 3.6 0 4.9.07 1.2.05 1.8.25 2.2.42.6.22 1 .48 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c0 1.2-.2 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2 0-1.8-.2-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c0-1.2.2-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4 1.3-.1 1.7-.1 4.8-.1Z"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return '<svg class="icon icon--' . esc_attr( $name ) . '" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/* -------------------------------------------------------------------------
 * Logo
 * ---------------------------------------------------------------------- */

function libreriatres_logo( $class = 'logo' ) {
	$title = get_bloginfo( 'name' );

	$html = '<a class="' . esc_attr( $class ) . '" href="' . esc_url( home_url( '/' ) ) . '" rel="home">'
		. '<img class="logo__mark" src="' . esc_url( LIBRERIA_TRES_URI . '/assets/img/logo.png' ) . '" width="519" height="519" alt="" decoding="async">'
		. '<span class="logo__text">'
		. '<span class="logo__name">' . esc_html( $title ) . '</span>'
		. '<span class="logo__tagline">' . esc_html__( 'LIBRERÍA · PAPELERA · IMPRESIONES', 'libreriatres' ) . '</span>'
		. '</span></a>';

	return $html;
}

/**
 * Mariposa del logo (trazo magenta).
 */
function libreriatres_butterfly_svg( $size = 46 ) {
	return '<svg class="butterfly" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 64 64" fill="none" aria-hidden="true" focusable="false">'
		. '<path d="M31 27C28 11 15 4 9 12 3 20 12 31 30 31" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
		. '<path d="M33 27C36 11 49 4 55 12 61 20 52 31 34 31" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
		. '<path d="M30 33C22 37 15 43 19 50 23 57 31 46 31 36" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity=".8"/>'
		. '<path d="M34 33C42 37 49 43 45 50 41 57 33 46 33 36" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity=".8"/>'
		. '<path d="M32 15v34" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'
		. '<path d="M32 15c-1-6-4-9-8-10M32 15c1-6 4-9 8-10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'
		. '</svg>';
}

/* -------------------------------------------------------------------------
 * Menu mobile
 * ---------------------------------------------------------------------- */

function libreriatres_mobile_menu() {
	?>
	<div class="mobile-menu" id="mobileMenu" hidden>
		<div class="mobile-menu__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'libreriatres' ); ?>">
			<button type="button" class="mobile-menu__close" data-menu-close aria-label="<?php esc_attr_e( 'Cerrar menu', 'libreriatres' ); ?>">
				<?php echo libreriatres_icon( 'close', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'mobile-menu__list',
						'depth'          => 2,
					)
				);
			} else {
				wp_nav_menu(
					array(
						'container'  => false,
						'menu_class' => 'mobile-menu__list',
						'depth'      => 2,
					)
				);
			}
			?>
			<div class="mobile-menu__contact">
				<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', libreriatres_phone_display() ) ); ?>">
					<?php echo libreriatres_icon( 'chat', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( libreriatres_phone_display() ); ?>
				</a>
				<span><?php echo libreriatres_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( libreriatres_address() ); ?></span>
			</div>
		</div>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * WooCommerce
 * ---------------------------------------------------------------------- */

/**
 * Quita el sidebar de WooCommerce y ajusta breadcrumb.
 */
function libreriatres_woo_setup() {
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar' );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
}
add_action( 'init', 'libreriatres_woo_setup' );

/**
 * Muestra el breadcrumb propio.
 */
function libreriatres_woo_breadcrumb() {
	if ( is_shop() || is_product_taxonomy() || is_product() ) {
		echo '<nav class="breadcrumb" aria-label="' . esc_attr__( 'Miga de pan', 'libreriatres' ) . '">';
		echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Inicio', 'libreriatres' ) . '</a>';
		if ( is_shop() ) {
			echo '<span class="breadcrumb__sep">›</span><span>' . esc_html( woocommerce_page_title( false ) ) . '</span>';
		} elseif ( is_product_taxonomy() ) {
			$term = get_queried_object();
			echo '<span class="breadcrumb__sep">›</span><span>' . esc_html( $term->name ) . '</span>';
		} elseif ( is_product() ) {
			echo '<span class="breadcrumb__sep">›</span>';
			echo '<a href="' . esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '">' . esc_html__( 'Productos', 'libreriatres' ) . '</a>';
			echo '<span class="breadcrumb__sep">›</span><span>' . esc_html( get_the_title() ) . '</span>';
		}
		echo '</nav>';
	}
}
add_action( 'woocommerce_before_main_content', 'libreriatres_woo_breadcrumb', 20 );

/**
 * Cantidad de articulos en el carrito (fragmento AJAX).
 */
function libreriatres_cart_fragments( $fragments ) {
	$count = 0;
	if ( function_exists( 'WC' ) && WC()->cart ) {
		$count = WC()->cart->get_cart_contents_count();
	}
	ob_start();
	?>
	<span class="cart-count" data-cart-count><?php echo (int) $count; ?></span>
	<?php
	$fragments['span[data-cart-count]'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'libreriatres_cart_fragments' );

/**
 * Cart items count en el header.
 */
function libreriatres_header_cart_count() {
	$count = 0;
	if ( function_exists( 'WC' ) && WC()->cart ) {
		$count = WC()->cart->get_cart_contents_count();
	}
	return (int) $count;
}

/**
 * Precio en pesos sin decimales.
 */
function libreriatres_price( $price ) {
	if ( function_exists( 'wc_price' ) ) {
		return wc_price( $price );
	}
	return '$' . number_format( (float) $price, 0, ',', '.' );
}

/**
 * Quita el "sale price" duplicado en listados: dejamos un solo precio.
 */
function libreriatres_woocommerce_pagination_args( $args ) {
	$args['prev_text'] = '‹';
	$args['next_text'] = '›';
	return $args;
}
add_filter( 'woocommerce_pagination_args', 'libreriatres_woocommerce_pagination_args' );

/**
 * Ajustes de paginacion de WooCommerce.
 */
add_filter( 'woocommerce_show_page_title', '__return_true' );

/**
 * Cambia el texto del boton "Anadir al carrito" a "Consultanos por WhatsApp" cuando
 * el producto es simple (segn el diseno, el CTA principal es WhatsApp).
 */
function libreriatres_loop_add_to_cart_text( $text, $product ) {
	return esc_html__( 'Consultanos por WhatsApp', 'libreriatres' );
}
add_filter( 'woocommerce_product_add_to_cart_text', 'libreriatres_loop_add_to_cart_text' );

/**
 * Oculta el boton "Anadir al carrito" del loop: el CTA es WhatsApp.
 */
function libreriatres_loop_hide_add_to_cart() {
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
}
add_action( 'init', 'libreriatres_loop_hide_add_to_cart' );

/* -------------------------------------------------------------------------
 * Utilidades varias
 * ---------------------------------------------------------------------- */

/**
 * ¿Estamos en una pagina de WooCommerce?
 */
function libreriatres_is_woo() {
	return function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() );
}

/**
 * Devuelve los ids de los productos que estan en el carrito.
 *
 * El HTML de la pagina puede venir de la cache (LiteSpeed), asi que el estado
 * "en el carrito" de las cards se consulta por AJAX en vez de renderizarlo.
 */
function libreriatres_ajax_cart_items() {
	$ids = array();

	if ( function_exists( 'WC' ) && WC()->cart ) {
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( ! empty( $item['product_id'] ) ) {
				$ids[] = (int) $item['product_id'];
			}
		}
	}

	wp_send_json_success( array( 'ids' => array_values( array_unique( $ids ) ) ) );
}
add_action( 'wp_ajax_libreriatres_cart_items', 'libreriatres_ajax_cart_items' );
add_action( 'wp_ajax_nopriv_libreriatres_cart_items', 'libreriatres_ajax_cart_items' );

/**
 * Excerpt mas corto.
 */
function libreriatres_excerpt_length() {
	return 22;
}
add_filter( 'excerpt_length', 'libreriatres_excerpt_length' );

/* -------------------------------------------------------------------------
 * Piezas de tienda
 * ---------------------------------------------------------------------- */

/**
 * Encabezado de la pagina de tienda.
 */
function libreriatres_shop_header() {
	if ( ! function_exists( 'is_shop' ) || ! is_shop() ) {
		return;
	}
	?>
	<div class="shop-hero">
		<p><?php esc_html_e( 'Todos los productos de Libreria Tres: útiles escolares, papelería, impresiones y combos. Escribinos por WhatsApp para consultar stock y precios.', 'libreriatres' ); ?></p>
	</div>
	<?php
}
add_action( 'woocommerce_before_shop_loop', 'libreriatres_shop_header', 5 );

/**
 * Boton de pedido por WhatsApp en la pagina de producto.
 */
function libreriatres_single_product_whatsapp() {
	global $product;

	if ( ! $product ) {
		return;
	}

	$msg = sprintf(
		/* translators: 1: nombre del producto, 2: precio. */
		__( '¡Hola! Consulto por el producto "%1$s" (%2$s). ¿Me contás disponibilidad?', 'libreriatres' ),
		$product->get_name(),
		html_entity_decode( wp_strip_all_tags( $product->get_price_html() ), ENT_QUOTES, 'UTF-8' )
	);
	?>
	<div class="single-product__wa">
		<?php echo libreriatres_whatsapp_link( $msg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'libreriatres_single_product_whatsapp', 35 );

/**
 * Bloque "enviar pedido por WhatsApp" al final del carrito.
 */
function libreriatres_cart_whatsapp_box() {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return;
	}

	$count = 0;
	if ( function_exists( 'WC' ) && WC()->cart ) {
		$count = WC()->cart->get_cart_contents_count();
	}

	if ( $count < 1 ) {
		return;
	}
	?>
	<div class="wa-order-box">
		<h3><?php esc_html_e( 'Cerrá el pedido por WhatsApp', 'libreriatres' ); ?></h3>
		<p><?php esc_html_e( 'Te armamos el mensaje con todos los productos y el total. Solo tenés que enviarlo y te confirmamos stock, forma de pago y entrega.', 'libreriatres' ); ?></p>
		<a class="btn btn--whatsapp btn--block" href="#" data-wa-order>
			<span class="btn__icon" aria-hidden="true"><?php echo libreriatres_icon( 'whatsapp', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="btn__label"><?php esc_html_e( 'Enviar pedido por WhatsApp', 'libreriatres' ); ?></span>
		</a>
		<p class="wa-order-box__note">
			<?php esc_html_e( 'Horario de atención: Lunes a Sábados 14 a 22 hs. También podés pasar por el local.', 'libreriatres' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'woocommerce_after_cart', 'libreriatres_cart_whatsapp_box' );

/**
 * Aviso arriba del carrito: la compra se coordina por WhatsApp.
 */
function libreriatres_cart_notice() {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return;
	}
	?>
	<div class="cart-notice">
		<?php echo libreriatres_icon( 'chat', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<p>
			<?php esc_html_e( 'No tenemos checkout online: armá tu pedido y cerralo con nosotros por WhatsApp. Te respondemos con stock, forma de pago y envío.', 'libreriatres' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'woocommerce_before_cart_table', 'libreriatres_cart_notice', 5 );

/**
 * Saca del carrito todo lo que sea la pasarela de pago online.
 */
function libreriatres_cart_remove_checkout() {
	// Codigo de descuento.
	remove_action( 'woocommerce_before_cart_table', 'woocommerce_show_cart_coupons', 10 );
	// Boton "Proceder al checkout".
	remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_checkout_form_tag', 20 );
	remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
	// Aviso de stock.
	remove_action( 'woocommerce_before_cart', 'woocommerce_stock_notice' );
}
add_action( 'init', 'libreriatres_cart_remove_checkout' );

/**
 * Aviso de envio de pedido en la pagina de gracias.
 */
function libreriatres_thankyou_whatsapp() {
	if ( ! function_exists( 'is_order_received' ) || ! is_order_received() ) {
		return;
	}
	?>
	<div class="wa-order-box">
		<h3><?php esc_html_e( '¿Necesitás algo más?', 'libreriatres' ); ?></h3>
		<p><?php esc_html_e( 'Escribinos y te ayudamos con cualquier consulta del pedido.', 'libreriatres' ); ?></p>
		<?php
		echo libreriatres_whatsapp_link( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			__( '¡Hola! Acabo de hacer un pedido en Libreria Tres.', 'libreriatres' )
		);
		?>
	</div>
	<?php
}
add_action( 'woocommerce_thankyou', 'libreriatres_thankyou_whatsapp', 20 );


/**
 * Paginacion numerada.
 */
function libreriatres_pagination() {
	$links = paginate_links(
		array(
			'type'      => 'array',
			'prev_text' => '‹',
			'next_text' => '›',
		)
	);
	if ( ! $links ) {
		return;
	}
	echo '<nav class="pagination" aria-label="' . esc_attr__( 'Paginacion', 'libreriatres' ) . '"><ul>';
	foreach ( $links as $link ) {
		echo '<li>' . wp_kses_post( $link ) . '</li>';
	}
	echo '</ul></nav>';
}

/**
 * Encola el script del tema tambien en el login de WP para que no rompa.
 */
function libreriatres_body_class( $classes ) {
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'libreriatres_body_class' );

/**
 * Sitemap / robots: nada extra.
 */
function libreriatres_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'libreriatres_excerpt_more' );
