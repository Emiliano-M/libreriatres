<?php
/**
 * Plugin Name: Libreria Tres Core
 * Plugin URI: https://forestgreen-coyote-929041.hostingersite.com
 * Description: Arranque y configuracion de la tienda Libreria Tres: limpia el contenido de demo, crea paginas, menu, categorias, productos y ajusta WooCommerce.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Libreria Tres
 * Text Domain: libreriatres-core
 *
 * @package Libreria_Tres_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LIBRERIA_TRES_CORE_VERSION', '1.0.0' );
define( 'LIBRERIA_TRES_CORE_FILE', __FILE__ );
define( 'LIBRERIA_TRES_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'LIBRERIA_TRES_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once LIBRERIA_TRES_CORE_DIR . 'inc/class-options.php';
require_once LIBRERIA_TRES_CORE_DIR . 'inc/class-cleanup.php';
require_once LIBRERIA_TRES_CORE_DIR . 'inc/class-media.php';
require_once LIBRERIA_TRES_CORE_DIR . 'inc/class-seed.php';
require_once LIBRERIA_TRES_CORE_DIR . 'inc/class-commerce.php';
require_once LIBRERIA_TRES_CORE_DIR . 'inc/class-admin.php';

/**
 * Arranque.
 */
final class Libreria_Tres_Core {

	/**
	 * Registro de hooks.
	 */
	public static function init() {
		Libreria_Tres_Core_Admin::init();
		Libreria_Tres_Core_Commerce::init();
	}

	/**
	 * Ejecuta el bootstrap una sola vez (limpieza + seed + configuracion).
	 *
	 * @return bool True si se ejecuto en esta llamada.
	 */
	public static function bootstrap( $force = false ) {
		$done = get_option( 'libreriatres_core_version' );

		if ( ! $force && $done === LIBRERIA_TRES_CORE_VERSION ) {
			return false;
		}

		// Evitar ejecuciones simultaneas.
		if ( get_option( 'libreriatres_core_running' ) && ! $force ) {
			return false;
		}
		update_option( 'libreriatres_core_running', 1, false );

		try {
			Libreria_Tres_Core_Options::defaults();
			Libreria_Tres_Core_Cleanup::run();
			Libreria_Tres_Core_Seed::ensure_theme();
			Libreria_Tres_Core_Seed::run();
			Libreria_Tres_Core_Options::woocommerce();

			update_option( 'libreriatres_core_version', LIBRERIA_TRES_CORE_VERSION );
			update_option( 'libreriatres_core_log', gmdate( 'c' ) );
		} catch ( \Throwable $e ) {
			update_option( 'libreriatres_core_error', $e->getMessage() );
		} finally {
			delete_option( 'libreriatres_core_running' );
		}

		return true;
	}
}

Libreria_Tres_Core::init();

/**
 * El bootstrap corre en la activacion y tambien de forma diferida en el primer
 * request (por si la activacion via API no dispara el hook).
 *
 * Se engancha en 'wp_loaded' para que WooCommerce ya este cargado (hace falta
 * para crear productos y aplicar los ajustes de la tienda).
 */
register_activation_hook(
	__FILE__,
	function () {
		Libreria_Tres_Core::bootstrap();
	}
);

add_action(
	'wp_loaded',
	function () {
		if ( get_option( 'libreriatres_core_version' ) !== LIBRERIA_TRES_CORE_VERSION ) {
			Libreria_Tres_Core::bootstrap();
		}
	},
	20
);
