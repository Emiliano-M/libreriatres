<?php
/**
 * Feed de Instagram para la seccion de la home.
 *
 * Fuentes soportadas (se usa la primera que este configurada):
 *  1. RSS: una URL de feed (por ejemplo el que genera RSS.app para la cuenta).
 *  2. Graph API de Instagram con un access token.
 *
 * Sin ninguna de las dos, la seccion sigue mostrando los placeholders: el
 * sitio nunca se rompe.
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
	 * Transient donde se cachea el feed.
	 */
	const CACHE_KEY = 'libreriatres_instagram_feed';

	/**
	 * Opcion persistente de respaldo para que las publicaciones no desaparezcan si falla un refresco.
	 */
	const BACKUP_KEY = 'libreriatres_instagram_feed_backup';

	/**
	 * Cuanto se cachea el feed (6 horas).
	 */
	const TTL = 21600;

	/**
	 * Cada cuanto se renueva automaticamente el token de larga duracion (15 dias).
	 */
	const TOKEN_REFRESH_INTERVAL = 1296000;

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
	 * Devuelve el feed cacheado (o el ultimo respaldo valido si el transient expiro).
	 *
	 * @return array
	 */
	public static function get_feed() {
		$feed = get_transient( self::CACHE_KEY );

		if ( is_array( $feed ) && ! empty( $feed ) ) {
			return $feed;
		}

		$backup = get_option( self::BACKUP_KEY, array() );

		return is_array( $backup ) ? $backup : array();
	}

	/**
	 * URL del feed RSS configurada.
	 */
	public static function get_rss_url() {
		return trim( (string) get_option( 'libreriatres_instagram_rss', '' ) );
	}

	/**
	 * Token de la Graph API configurado.
	 */
	public static function get_token() {
		return trim( (string) get_option( 'libreriatres_instagram_token', '' ) );
	}

	/**
	 * Refresca el feed con la fuente disponible.
	 * Prioriza el token oficial de Graph API; si no esta o falla, intenta con RSS.
	 *
	 * @return array|WP_Error
	 */
	public static function refresh() {
		$posts = false;

		if ( self::get_token() ) {
			$posts = self::refresh_graph();
		}

		if ( ( false === $posts || is_wp_error( $posts ) ) && self::get_rss_url() ) {
			$rss_posts = self::refresh_rss();
			if ( ! is_wp_error( $rss_posts ) || false === $posts ) {
				$posts = $rss_posts;
			}
		}

		if ( false === $posts ) {
			delete_option( 'libreriatres_instagram_error' );
			// Sin fuente configurada no queda un feed viejo dando vueltas.
			delete_transient( self::CACHE_KEY );
			delete_option( self::BACKUP_KEY );
			delete_option( 'libreriatres_instagram_media' );

			return new WP_Error( 'sin_fuente', __( 'Configurá un token de Instagram o un feed RSS.', 'libreriatres-core' ) );
		}

		if ( is_wp_error( $posts ) ) {
			update_option( 'libreriatres_instagram_error', $posts->get_error_message() );

			// Si ya habia un feed guardado (transient o backup), se conserva y re-hidrata.
			$old = self::get_feed();
			if ( ! empty( $old ) ) {
				set_transient( self::CACHE_KEY, $old, self::TTL );
				return $old;
			}

			return $posts;
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
		update_option( self::BACKUP_KEY, $posts, false );
		set_transient( self::CACHE_KEY, $posts, self::TTL );
		delete_option( 'libreriatres_instagram_error' );

		// La pagina esta cacheada (LiteSpeed): sin purgar, se seguirian
		// viendo las publicaciones anteriores.
		self::purge_cache();

		return $posts;
	}

	/**
	 * Limpia la cache del sitio para que se vea el feed nuevo.
	 */
	public static function purge_cache() {
		wp_cache_flush();

		if ( class_exists( '\LiteSpeed\Purge' ) && method_exists( '\LiteSpeed\Purge', 'purge_all' ) ) {
			call_user_func( array( '\LiteSpeed\Purge', 'purge_all' ) );
		}

		do_action( 'litespeed_purge_all' );
		do_action( 'libreriatres_purge_cache' );
	}

	/* ------------------------------------------------------------------ */
	/* Fuente RSS                                                          */
	/* ------------------------------------------------------------------ */

	/**
	 * Descarga y parsea el feed RSS.
	 *
	 * @return array|WP_Error
	 */
	public static function refresh_rss() {
		$url = self::get_rss_url();

		if ( ! $url ) {
			return new WP_Error( 'sin_rss', __( 'Falta la URL del feed RSS.', 'libreriatres-core' ) );
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 20,
				'user-agent' => 'Libreria Tres / ' . home_url( '/' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error(
				'rss_http',
				sprintf(
					/* translators: %d: codigo HTTP. */
					__( 'El feed RSS respondió con un error HTTP %d.', 'libreriatres-core' ),
					(int) wp_remote_retrieve_response_code( $response )
				)
			);
		}

		return self::parse_rss( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Parsea un XML de RSS/Atom y devuelve las publicaciones.
	 *
	 * @param string $xml Contenido del feed.
	 * @return array|WP_Error
	 */
	public static function parse_rss( $xml ) {
		$xml = trim( (string) $xml );

		if ( '' === $xml ) {
			return new WP_Error( 'rss_vacio', __( 'El feed RSS vino vacío.', 'libreriatres-core' ) );
		}

		// Algunos feeds vienen con BOM o junk antes del XML.
		$start = strpos( $xml, '<' );
		if ( false !== $start ) {
			$xml = substr( $xml, $start );
		}

		$previous = libxml_use_internal_errors( true );
		$feed     = simplexml_load_string( $xml );
		libxml_use_internal_errors( $previous );

		if ( false === $feed ) {
			return new WP_Error( 'rss_xml', __( 'No se pudo leer el XML del feed.', 'libreriatres-core' ) );
		}

		$items = isset( $feed->channel->item ) ? $feed->channel->item : ( isset( $feed->item ) ? $feed->item : array() );

		if ( empty( $items ) ) {
			return new WP_Error( 'rss_sin_items', __( 'El feed no tiene publicaciones.', 'libreriatres-core' ) );
		}

		$posts = array();

		foreach ( $items as $item ) {
			$link = self::item_link( $item );

			if ( ! $link ) {
				continue;
			}

			$image = self::item_image( $item );

			if ( ! $image ) {
				continue;
			}

			$caption = self::item_caption( $item );

			$posts[] = array(
				'id'        => md5( $link ),
				'permalink' => esc_url_raw( $link ),
				'image'     => esc_url_raw( $image ),
				'caption'   => $caption,
				'date'      => self::item_date( $item ),
			);

			if ( count( $posts ) >= 4 ) {
				break;
			}
		}

		if ( empty( $posts ) ) {
			return new WP_Error( 'rss_sin_fotos', __( 'El feed no tiene publicaciones con imagen.', 'libreriatres-core' ) );
		}

		return $posts;
	}

	/**
	 * Link del post.
	 */
	private static function item_link( $item ) {
		if ( isset( $item->link ) ) {
			$link = (string) $item->link;
			if ( $link ) {
				return $link;
			}
		}

		// Atom: link con rel="alternate".
		foreach ( $item->children( 'http://www.w3.org/2005/Atom' ) as $atom_link ) {
			$attrs = $atom_link->attributes();
			if ( ! empty( $attrs['rel'] ) && 'alternate' === (string) $attrs['rel'] && ! empty( $attrs['href'] ) ) {
				return (string) $attrs['href'];
			}
		}

		return '';
	}

	/**
	 * URL de la imagen del post (media:content, media:thumbnail, enclosure o <img>).
	 */
	private static function item_image( $item ) {
		// 1) media:content / media:thumbnail.
		foreach ( $item->children( 'http://search.yahoo.com/mrss/' ) as $child ) {
			$name = strtolower( $child->getName() );

			if ( in_array( $name, array( 'content', 'thumbnail' ), true ) ) {
				$attrs = $child->attributes();
				if ( ! empty( $attrs['url'] ) ) {
					return html_entity_decode( (string) $attrs['url'] );
				}
			}
		}

		// 2) enclosure.
		if ( isset( $item->enclosure ) ) {
			$attrs = $item->enclosure->attributes();
			if ( ! empty( $attrs['url'] ) ) {
				return html_entity_decode( (string) $attrs['url'] );
			}
		}

		// 3) <img> dentro de description / content:encoded.
		foreach ( array( 'description', 'encoded' ) as $field ) {
			if ( ! isset( $item->{$field} ) ) {
				continue;
			}

			$html = (string) $item->{$field};

			if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches ) ) {
				return html_entity_decode( $matches[1] );
			}
		}

		return '';
	}

	/**
	 * Caption / titulo del post.
	 */
	private static function item_caption( $item ) {
		$title = isset( $item->title ) ? wp_strip_all_tags( (string) $item->title ) : '';

		// El titulo suele venir como "Usuario on Instagram: "caption"".
		if ( false !== stripos( $title, ':' ) ) {
			$parts = explode( ':', $title, 2 );
			$title = trim( $parts[1] );
		}

		$title = trim( trim( $title ), '"' );

		return $title ? wp_trim_words( $title, 12, '…' ) : '';
	}

	/**
	 * Fecha de publicacion (timestamp).
	 */
	private static function item_date( $item ) {
		if ( isset( $item->pubDate ) ) {
			$time = strtotime( (string) $item->pubDate );
			if ( $time ) {
				return (int) $time;
			}
		}

		if ( isset( $item->date ) ) {
			$time = strtotime( (string) $item->date );
			if ( $time ) {
				return (int) $time;
			}
		}

		return 0;
	}

	/* ------------------------------------------------------------------ */
	/* Fuente Graph API                                                    */
	/* ------------------------------------------------------------------ */

	/**
	 * Descarga el feed desde la Graph API de Instagram.
	 *
	 * @return array|WP_Error
	 */
	public static function refresh_graph() {
		$token = self::get_token();

		if ( ! $token ) {
			return new WP_Error( 'sin_token', __( 'Falta el token de Instagram.', 'libreriatres-core' ) );
		}

		// Renueva automaticamente el token de larga duracion cada 15 dias para que no venza a los 60 dias.
		$token = self::maybe_refresh_token( $token );

		$version = trim( (string) get_option( 'libreriatres_instagram_version', 'v22.0' ) );
		$fields  = 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp';

		$response = self::request( $version, $fields, $token );

		// Si la version de la API no existe, reintenta sin version.
		if ( is_wp_error( $response ) && $version && false !== stripos( $response->get_error_message(), 'version' ) ) {
			$response = self::request( '', $fields, $token );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$items = isset( $response['data'] ) && is_array( $response['data'] ) ? $response['data'] : array();
		$posts = array();

		foreach ( $items as $item ) {
			$media_type = isset( $item['media_type'] ) ? strtoupper( (string) $item['media_type'] ) : 'IMAGE';

			// En videos/Reels, media_url es el archivo MP4; usamos thumbnail_url (portada en imagen).
			if ( 'VIDEO' === $media_type ) {
				$image = ! empty( $item['thumbnail_url'] ) ? $item['thumbnail_url'] : '';
			} else {
				$image = ! empty( $item['media_url'] ) ? $item['media_url'] : ( $item['thumbnail_url'] ?? '' );
			}

			if ( ! $image || empty( $item['permalink'] ) ) {
				continue;
			}

			$timestamp = 0;
			if ( ! empty( $item['timestamp'] ) ) {
				$parsed    = strtotime( (string) $item['timestamp'] );
				$timestamp = $parsed ? (int) $parsed : 0;
			}

			$posts[] = array(
				'id'        => (string) $item['id'],
				'permalink' => esc_url_raw( $item['permalink'] ),
				'image'     => esc_url_raw( $image ),
				'caption'   => isset( $item['caption'] ) ? wp_trim_words( (string) $item['caption'], 12, '…' ) : '',
				'date'      => $timestamp,
			);

			if ( count( $posts ) >= 4 ) {
				break;
			}
		}

		if ( empty( $posts ) ) {
			return new WP_Error( 'instagram_vacio', __( 'Instagram no devolvió publicaciones.', 'libreriatres-core' ) );
		}

		return $posts;
	}

	/**
	 * Renueva automaticamente el token de larga duracion de Instagram (dura 60 dias)
	 * si pasaron mas de 15 dias desde la ultima renovacion.
	 *
	 * @param string $token Token actual.
	 * @return string Token vigente.
	 */
	public static function maybe_refresh_token( $token ) {
		$last_refresh = (int) get_option( 'libreriatres_instagram_token_refreshed', 0 );

		if ( 0 === $last_refresh ) {
			update_option( 'libreriatres_instagram_token_refreshed', time(), false );
			return $token;
		}

		if ( ( time() - $last_refresh ) < self::TOKEN_REFRESH_INTERVAL ) {
			return $token;
		}

		$response = wp_remote_get(
			add_query_arg(
				array(
					'grant_type'   => 'ig_refresh_token',
					'access_token' => $token,
				),
				'https://graph.instagram.com/refresh_access_token'
			),
			array( 'timeout' => 15 )
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return $token;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! empty( $body['access_token'] ) && is_string( $body['access_token'] ) ) {
			$token = trim( $body['access_token'] );
			update_option( 'libreriatres_instagram_token', $token );
			update_option( 'libreriatres_instagram_token_refreshed', time(), false );
		}

		return $token;
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
					'fields'       => $fields,
					'limit'        => 12,
					'access_token' => $token,
				),
				$url
			),
			array( 'timeout' => 20 )
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $code || ! isset( $body['data'] ) ) {
			$message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Instagram no respondió.', 'libreriatres-core' );

			return new WP_Error( 'instagram', $message );
		}

		return $body;
	}

	/* ------------------------------------------------------------------ */
	/* Pruebas y estado                                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * Prueba la fuente configurada.
	 *
	 * @return array|WP_Error
	 */
	public static function test() {
		if ( self::get_token() ) {
			$result = self::refresh_graph();

			if ( ! is_wp_error( $result ) ) {
				return array( 'data' => array( 'count' => count( $result ) ), 'source' => 'graph' );
			}

			if ( ! self::get_rss_url() ) {
				return $result;
			}
		}

		if ( self::get_rss_url() ) {
			$result = self::refresh_rss();

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			return array( 'data' => array( 'count' => count( $result ) ), 'source' => 'rss' );
		}

		return new WP_Error( 'sin_fuente', __( 'Configurá un token de Instagram o un feed RSS.', 'libreriatres-core' ) );
	}

	/**
	 * Ultimo error guardado.
	 */
	public static function last_error() {
		return (string) get_option( 'libreriatres_instagram_error', '' );
	}
}
