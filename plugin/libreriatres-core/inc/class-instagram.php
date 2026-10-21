<?php
/**
 * Feed de Instagram para la seccion de la home.
 *
 * Usa la Graph API de Instagram (graph.instagram.com). Sin token la seccion
 * sigue mostrando los placeholders, asi que el sitio nunca se rompe.
 *
 * @package Libreria_Tres_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integracion con Instagram.
 */
class Libreria_Tres_Core_Instagram {

	/**
	 * Nombre del transient donde se cachea el feed.
	 */
	const CACHE_KEY = 'libreriatres_instagram_feed';

	/**
	 * Cuanto se cachea el feed (6 horas).
	 */
	const TTL = 21600;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'libreriatres_refresh_instagram', array( __CLASS__, 'refresh' ) );
		add_action( 'init', array( __CLASS__, 'schedule' ) );
	}

	/**
	 * Programa el refresco horario.
	 */
	public static function schedule() {
		if ( ! wp_next_scheduled( 'libreriatres_refresh_instagram' ) ) {
			wp_schedule_event( time() + 300, 'hourly', 'libreriatres_refresh_instagram' );
		}
	}

	/**
	 * Devuelve el feed cacheado (array de publicaciones).
	 *
	 * @return array
	 */
	public static function get_feed() {
		$feed = get_transient( self::CACHE_KEY );

		if ( ! is_array( $feed ) ) {
			return array();
		}

		return $feed;
	}

	/**
	 * Token configurado.
	 */
	public static function get_token() {
		return trim( (string) get_option( 'libreriatres_instagram_token', '' ) );
	}

	/**
	 * Refresca el feed desde la API y lo cachea.
	 *
	 * @return array|WP_Error Feed o error.
	 */
	public static function refresh() {
		$token = self::get_token();

		if ( ! $token ) {
			delete_option( 'libreriatres_instagram_error' );

			return new WP_Error( 'sin_token', __( 'Falta el token de Instagram.', 'libreriatres-core' ) );
		}

		$version = trim( (string) get_option( 'libreriatres_instagram_version', 'v22.0' ) );
		$fields  = 'id,caption,media_type,media_url,permalink,thumbnail_url';

		$response = self::request( $version, $fields, $token );

		// Si la version de la API no existe, reintenta sin version.
		if ( is_wp_error( $response ) && $version && false !== stripos( $response->get_error_message(), 'version' ) ) {
			$response = self::request( '', $fields, $token );
		}

		if ( is_wp_error( $response ) ) {
			update_option( 'libreriatres_instagram_error', $response->get_error_message() );

			// Si ya habia un feed cacheado, se conserva.
			$old = get_transient( self::CACHE_KEY );

			return is_array( $old ) ? $old : $response;
		}

		$posts = array();

		foreach ( $response->data as $item ) {
			// Solo imagenes: la seccion muestra fotos.
			if ( isset( $item['media_type'] ) && 'IMAGE' !== $item['media_type'] ) {
				continue;
			}

			$image = ! empty( $item['media_url'] ) ? $item['media_url'] : ( $item['thumbnail_url'] ?? '' );

			if ( ! $image || empty( $item['permalink'] ) ) {
				continue;
			}

			$posts[] = array(
				'id'        => (string) $item['id'],
				'permalink' => esc_url_raw( $item['permalink'] ),
				'image'     => esc_url_raw( $image ),
				'caption'   => isset( $item['caption'] ) ? wp_trim_words( (string) $item['caption'], 12, '…' ) : '',
				'date'      => isset( $item['timestamp'] ) ? (int) $item['timestamp'] : 0,
			);
		}

		// Copiamos las imagenes al hosting: no hacemos hotlink a Instagram.
		$map = (array) get_option( 'libreriatres_instagram_media', array() );

		foreach ( $posts as $index => $post ) {
			$attachment_id = isset( $map[ $post['id'] ] ) ? (int) $map[ $post['id'] ] : 0;

			// Reusamos la imagen si sigue en la biblioteca.
			if ( $attachment_id && get_post( $attachment_id ) && wp_get_attachment_url( $attachment_id ) ) {
				$posts[ $index ]['image'] = (string) wp_get_attachment_url( $attachment_id );
				continue;
			}

			$local = Libreria_Tres_Core_Media::sideload_url( $post['image'], $post['caption'] ? $post['caption'] : 'Instagram' );

			if ( $local ) {
				$posts[ $index ]['image'] = $local;

				$attachment_id = attachment_url_to_postid( $local );
				if ( $attachment_id ) {
					$map[ $post['id'] ] = $attachment_id;
				}
			}
		}

		update_option( 'libreriatres_instagram_media', $map );
		set_transient( self::CACHE_KEY, $posts, self::TTL );
		delete_option( 'libreriatres_instagram_error' );

		return $posts;
	}

	/**
	 * Consulta la Graph API.
	 *
	 * @param string $version Version de la API (vacio = la del servidor).
	 * @param string $fields  Campos a pedir.
	 * @param string $token   Token de acceso.
	 * @return array|WP_Error
	 */
	private static function request( $version, $fields, $token ) {
		$url = 'https://graph.instagram.com/' . ( $version ? $version . '/' : '' ) . 'me/media';

		$response = wp_remote_get(
			add_query_arg(
				array(
					'fields'      => $fields,
					'limit'       => 4,
					'access_token' => rawurlencode( $token ),
				),
				$url
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $code || ! isset( $body['data'] ) ) {
			$message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Instagram no respondio.', 'libreriatres-core' );

			return new WP_Error( 'instagram', $message );
		}

		return $body;
	}

	/**
	 * Prueba la conexion con el token configurado.
	 *
	 * @return array|WP_Error
	 */
	public static function test() {
		$token = self::get_token();

		if ( ! $token ) {
			return new WP_Error( 'sin_token', __( 'Falta el token de Instagram.', 'libreriatres-core' ) );
		}

		$response = self::request( trim( (string) get_option( 'libreriatres_instagram_version', 'v22.0' ) ), 'id,caption,media_type,media_url,permalink', $token );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $response;
	}

	/**
	 * Ultimo error guardado.
	 */
	public static function last_error() {
		return (string) get_option( 'libreriatres_instagram_error', '' );
	}
}
