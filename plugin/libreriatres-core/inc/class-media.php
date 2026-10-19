<?php
/**
 * Manejo de imagenes: copia las ilustraciones del plugin a la biblioteca de medios.
 *
 * @package Libreria_Tres_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utilidades de medios.
 */
class Libreria_Tres_Core_Media {

	/**
	 * Registra una imagen del plugin en la biblioteca de medios.
	 *
	 * @param string $filename Nombre del archivo dentro de assets.
	 * @param string $title    Titulo del adjunto.
	 * @param string $folder   Carpeta dentro de assets (products, etc).
	 * @return int ID del adjunto o 0.
	 */
	public static function sideload( $filename, $title, $folder = 'products' ) {
		$path   = 'assets/' . trim( $folder, '/' );
		$path   = 'assets/' === $path ? $path : $path . '/';
		$source = LIBRERIA_TRES_CORE_DIR . $path . $filename;

		if ( ! file_exists( $source ) ) {
			return 0;
		}

		$contents = file_get_contents( $source ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( false === $contents ) {
			return 0;
		}

		$upload = wp_upload_bits( $filename, null, $contents );

		if ( ! empty( $upload['error'] ) ) {
			return 0;
		}

		$filetype = wp_check_filetype( $upload['file'], null );

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'],
				'post_title'     => $title,
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$upload['file']
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $metadata );

		update_post_meta( $attachment_id, '_libreriatres_seeded', 1 );

		return (int) $attachment_id;
	}
}
