<?php
/**
 * Inspector de WordPress (temporal).
 * Uso: lt-wp.php?token=SECRETO&cmd=status
 */

$token = 'lt-9f2c1d';

if ( ! isset( $_GET['token'] ) || $_GET['token'] !== $token ) {
	http_response_code( 403 );
	exit( 'no' );
}

define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']    = 'libreriatres.com';
$_SERVER['REQUEST_URI']  = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once '/home/u352889191/domains/libreriatres.com/public_html/wp-load.php';

$cmd = isset( $_GET['cmd'] ) ? $_GET['cmd'] : 'status';

if ( 'status' === $cmd ) {
	echo "tema activo: " . get_stylesheet() . "\n";
	echo "tema dir: " . get_template_directory() . "\n";
	echo "plugins: " . implode( ', ', (array) get_option( 'active_plugins', array() ) ) . "\n";
	echo "core version: " . get_bloginfo( 'version' ) . "\n";
	echo "show_on_front: " . get_option( 'show_on_front' ) . " / page_on_front: " . get_option( 'page_on_front' ) . "\n";
	echo "core seed: " . get_option( 'libreriatres_core_version' ) . "\n";
	echo "core error: " . get_option( 'libreriatres_core_error' ) . "\n";
	echo "core log: " . get_option( 'libreriatres_core_log' ) . "\n";
	echo "paginas: " . wp_count_posts( 'page' )->publish . " | productos: " . wp_count_posts( 'product' )->publish . "\n";
	echo "categorias: " . wp_count_terms( array( 'taxonomy' => 'product_cat' ) ) . "\n";
	echo "adjuntos: " . wp_count_posts( 'attachment' )->inherit . "\n";
	echo "menus: " . implode( ', ', array_map( function ( $m ) { return $m->name; }, wp_get_nav_menus() ) ) . "\n";
	echo "woo currency: " . get_option( 'woocommerce_currency' ) . "\n";
	echo "shop page: " . get_option( 'woocommerce_shop_page_id' ) . " | cart: " . get_option( 'woocommerce_cart_page_id' ) . " | checkout: " . get_option( 'woocommerce_checkout_page_id' ) . "\n";

	$pages = get_option( 'libreriatres_pages', array() );
	foreach ( (array) $pages as $slug => $id ) {
		echo "  page $slug = $id (" . get_the_title( $id ) . ")\n";
	}
	echo "--- paginas publicadas ---\n";
	foreach ( get_posts( array( 'post_type' => 'page', 'numberposts' => 40, 'post_status' => 'publish' ) ) as $p ) {
		echo "  #{$p->ID} {$p->post_name} | {$p->post_title}\n";
	}
	echo "--- productos ---\n";
	foreach ( get_posts( array( 'post_type' => 'product', 'numberposts' => 40, 'post_status' => 'publish' ) ) as $p ) {
		$thumb = get_post_thumbnail_id( $p->ID );
		echo "  #{$p->ID} {$p->post_title} | " . get_post_meta( $p->ID, '_price', true ) . " | img:" . ( $thumb ? $thumb : 'sin' ) . "\n";
	}
	exit;
}

if ( 'reseed' === $cmd ) {
	delete_option( 'libreriatres_core_version' );
	if ( class_exists( 'Libreria_Tres_Core' ) ) {
		Libreria_Tres_Core::bootstrap( true );
		echo "reseed ok, error: " . get_option( 'libreriatres_core_error' ) . "\n";
	} else {
		echo 'plugin no cargado' . "\n";
	}
	exit;
}

if ( 'eval' === $cmd ) {
	// Ejecuta una consulta SQL de solo lectura.
	global $wpdb;
	$sql = isset( $_GET['sql'] ) ? $_GET['sql'] : '';
	if ( ! preg_match( '/^\s*(SELECT|SHOW)\s/i', $sql ) ) {
		exit( 'solo SELECT' );
	}
	$sql = str_replace( 'wp_', $wpdb->prefix, $sql );
	$rows = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( (array) $rows as $row ) {
		echo wp_json_encode( $row ) . "\n";
	}
	exit;
}

if ( 'cleanup' === $cmd ) {
	// Borra los archivos temporales de depuracion.
	$root = '/home/u352889191/domains/libreriatres.com/public_html';
	$files = glob( $root . '/lt-*.php' );
	$files = array_merge( $files, glob( $root . '/wp-content/mu-plugins/lt-*.php' ) );

	foreach ( $files as $file ) {
		echo ( unlink( $file ) ? 'borrado ' : 'error ' ) . basename( $file ) . "\n";
	}

	// Borra el log de errores.
	$log = $root . '/wp-content/lt-error.log';
	if ( file_exists( $log ) ) {
		echo ( unlink( $log ) ? 'borrado ' : 'error ' ) . 'lt-error.log' . "\n";
	}

	// Archivos de prueba.
	foreach ( array( 'test-feed.xml' ) as $extra ) {
		$file = $root . '/' . $extra;
		if ( file_exists( $file ) ) {
			echo ( unlink( $file ) ? 'borrado ' : 'error ' ) . $extra . "\n";
		}
	}

	// Carpetas del tema que quedaron con sufijo -old-<hash>.
	$stale = glob( $root . '/wp-content/themes/libreriatres-old-*' );
	foreach ( $stale as $dir ) {
		$items = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ( $items as $item ) {
			if ( $item->isDir() ) {
				@rmdir( $item->getPathname() );
			} else {
				@unlink( $item->getPathname() );
			}
		}
		echo ( @rmdir( $dir ) ? 'borrado ' : 'error ' ) . basename( $dir ) . "\n";
	}

	exit;
}

if ( 'instagram' === $cmd ) {
	if ( isset( $_GET['ig_token'] ) ) {
		update_option( 'libreriatres_instagram_token', sanitize_text_field( $_GET['ig_token'] ) );
	}
	if ( isset( $_GET['ig_rss'] ) ) {
		update_option( 'libreriatres_instagram_rss', esc_url_raw( $_GET['ig_rss'] ) );
	}
	$feed = Libreria_Tres_Core_Instagram::refresh();
	echo is_wp_error( $feed ) ? 'ERROR: ' . $feed->get_error_message() : 'OK ' . count( $feed ) . ' publicaciones';
	echo "\nfeed cache: " . count( Libreria_Tres_Core_Instagram::get_feed() );
	echo "\nultimo error: " . Libreria_Tres_Core_Instagram::last_error();
	echo "\nrss: " . ( Libreria_Tres_Core_Instagram::get_rss_url() ? Libreria_Tres_Core_Instagram::get_rss_url() : 'vacio' );
	foreach ( Libreria_Tres_Core_Instagram::get_feed() as $p ) {
		echo "\n  - " . $p['permalink'] . " | " . $p['image'] . " | " . $p['caption'];
	}
	exit;
}

echo "comando desconocido\n";
