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
	 * Descarga una imagen remota y la importa a la biblioteca de medios.
	 *
	 * Se usa para las fotos de Instagram: asi no hacemos hotlink a Instagram
	 * y las imagenes quedan cacheadas en el hosting.
	 *
	 * @param string $url   URL de la imagen.
	 * @param string $title Titulo del adjunto.
	 * @return string URL local de la imagen o '' si fallo.
	 */
	public static function sideload_url( $url, $title = '' ) {
		if ( ! $url ) {
			return '';
		}

		$response = wp_remote_get( $url, array( 'timeout' => 20 ) );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			return '';
		}

		$body = wp_remote_retrieve_body( $response );
		$type = (string) wp_remote_retrieve_header( $response, 'content-type' );

		if ( ! $body || 0 === strpos( $type, 'image/' ) ) {
			return '';
		}

		$name      = sanitize_file_name( basename( wp_parse_url( $url, PHP_URL_PATH ) ) );
		$extension = pathinfo( $name, PATHINFO_EXTENSION );

		if ( ! $extension ) {
			$extension = 'jpg';
		}

		$upload = wp_upload_bits( $name, null, $body );

		if ( ! empty( $upload['error'] ) ) {
			return '';
		}

		$filetype = wp_check_filetype( $upload['file'], null );

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/jpeg',
				'post_title'     => $title ? $title : 'Instagram',
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$upload['file']
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return '';
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $metadata );

		update_post_meta( $attachment_id, '_libreriatres_instagram', 1 );

		return (string) wp_get_attachment_url( $attachment_id );
	}

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
