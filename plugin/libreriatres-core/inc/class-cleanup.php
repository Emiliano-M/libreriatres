<?php
/**
 * Limpieza del contenido de demo (sitio de plantas "Urban Jungle Co.").
 *
 * @package Libreria_Tres_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Borra el contenido preexistente para dejar la tienda limpia.
 */
class Libreria_Tres_Core_Cleanup {

	/**
	 * Ejecuta la limpieza.
	 */
	public static function run() {
		self::delete_posts();
		self::delete_attachments();
		self::delete_product_terms();
		self::delete_menus();
		self::reset_widgets();
		self::delete_demo_options();
		self::remove_probe_files();
		self::remove_stale_theme_dirs();
		self::reset_permalinks();
	}

	/**
	 * Borra archivos temporales que hayan quedado en la carpeta del tema.
	 */
	private static function remove_probe_files() {
		$dir = get_template_directory();

		if ( ! is_dir( $dir ) ) {
			return;
		}

		foreach ( (array) glob( $dir . '/__probe*' ) as $file ) {
			if ( is_file( $file ) ) {
				@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
		}
	}

	/**
	 * Borra las carpetas del tema que quedaron con sufijo "-old-<hash>"
	 * (las genera el file browser de Hostinger cuando una carpeta ya existe).
	 */
	public static function remove_stale_theme_dirs() {
		$base = WP_CONTENT_DIR . '/themes';

		if ( ! is_dir( $base ) ) {
			return;
		}

		foreach ( (array) glob( $base . '/libreriatres-old-*' ) as $dir ) {
			if ( is_dir( $dir ) ) {
				self::rmdir_recursive( $dir );
			}
		}
	}

	/**
	 * Borra un directorio recursivamente.
	 */
	private static function rmdir_recursive( $dir ) {
		$items = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $items as $item ) {
			if ( $item->isDir() ) {
				@rmdir( $item->getPathname() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			} else {
				@unlink( $item->getPathname() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
		}

		@rmdir( $dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Borra entradas, paginas y productos.
	 */
	private static function delete_posts() {
		$post_types = array( 'post', 'page', 'product', 'product_variation' );

		$posts = get_posts(
			array(
				'post_type'        => $post_types,
				'post_status'      => 'any',
				'numberposts'      => -1,
				'suppress_filters' => true,
				'fields'           => 'ids',
			)
		);

		foreach ( $posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}
	}

	/**
	 * Borra la biblioteca de medios.
	 */
	private static function delete_attachments() {
		$attachments = get_posts(
			array(
				'post_type'        => 'attachment',
				'post_status'      => 'any',
				'numberposts'      => -1,
				'suppress_filters' => true,
				'fields'           => 'ids',
			)
		);

		foreach ( $attachments as $attachment_id ) {
			wp_delete_attachment( $attachment_id, true );
		}
	}

	/**
	 * Borra las categorias de producto de la demo.
	 */
	private static function delete_product_terms() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return;
		}

		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, 'product_cat' );
		}

		// Etiquetas de producto tambien.
		$tags = get_terms(
			array(
				'taxonomy'   => 'product_tag',
				'hide_empty' => false,
			)
		);
		if ( ! is_wp_error( $tags ) ) {
			foreach ( $tags as $tag ) {
				wp_delete_term( $tag->term_id, 'product_tag' );
			}
		}
	}

	/**
	 * Borra los menus de navegacion.
	 */
	private static function delete_menus() {
		$menus = wp_get_nav_menus();
		if ( ! empty( $menus ) ) {
			foreach ( $menus as $menu ) {
				wp_delete_nav_menu( $menu->term_id );
			}
		}
	}

	/**
	 * Limpia los widgets.
	 */
	private static function reset_widgets() {
		update_option( 'sidebars_widgets', array() );

		$widgets = array(
			'widget_block',
			'widget_calendar',
			'widget_categories',
			'widget_custom_html',
			'widget_media_image',
			'widget_nav_menu',
			'widget_pages',
			'widget_post_content',
			'widget_recent_comments',
			'widget_recent_posts',
			'widget_rss',
			'widget_search',
			'widget_tag_cloud',
			'widget_text',
			'widget_searchform',
		);

		foreach ( $widgets as $widget ) {
			update_option( $widget, array() );
		}
	}

	/**
	 * Borra opciones de la demo (Astra, Elementor, WooCommerce demo).
	 */
	private static function delete_demo_options() {
		$exact = array(
			'astra_settings',
			'elementor_global_css',
			'elementor_css',
			'elementor_pro_experimentals',
			'elementor_tracker_notice',
			'elementor_experimentals',
			'woocommerce_demo_products',
			'woocommerce_demo_products',
			'woocommerce_placeholder_image',
			'woocommerce_placeholder_image_150x150',
			'woocommerce_placeholder_image_300x300',
			'woocommerce_placeholder_image_1024x1024',
			'surerank_activated',
			'wpcodebox_snippet',
			'wpcodebox_settings',
			'hide-this-element',
		);

		foreach ( $exact as $option ) {
			delete_option( $option );
		}

		// Opciones que empiezan con estos prefijos.
		global $wpdb;
		$prefixes = array(
			'elementor_%',
			'astra_',
			'wpcodebox_',
			'elementor_library_%',
			'_transient_woocommerce_%',
		);

		foreach ( $prefixes as $prefix ) {
			$like = $wpdb->esc_like( $prefix ) . '%';
			$rows = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			if ( ! empty( $rows ) ) {
				foreach ( $rows as $row ) {
					delete_option( $row );
				}
			}
		}
	}

	/**
	 * Limpia la cache de permalinks.
	 */
	private static function reset_permalinks() {
		global $wp_rewrite;
		if ( isset( $wp_rewrite ) ) {
			$wp_rewrite->flush_rules( false );
		}
	}
}
