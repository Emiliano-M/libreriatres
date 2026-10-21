<?php
/**
 * Panel de administracion y shortcodes.
 *
 * @package Libreria_Tres_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin del plugin.
 */
class Libreria_Tres_Core_Admin {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_post_libreriatres_reseed', array( __CLASS__, 'handle_reseed' ) );
		add_action( 'admin_post_libreriatres_instagram_test', array( __CLASS__, 'handle_instagram_test' ) );
		add_shortcode( 'libreriatres_contacto', array( __CLASS__, 'contacto_shortcode' ) );
	}

	/**
	 * Agrega el menu del plugin.
	 */
	public static function menu() {
		add_menu_page(
			'Librería Tres',
			'Librería Tres',
			'manage_options',
			'libreriatres',
			array( __CLASS__, 'render_page' ),
			'dashicons-heart',
			58
		);
	}

	/**
	 * Registra los ajustes.
	 */
	public static function register_settings() {
		$fields = array(
			'libreriatres_whatsapp',
			'libreriatres_phone',
			'libreriatres_address',
			'libreriatres_hours',
			'libreriatres_instagram',
		);

		foreach ( $fields as $field ) {
			register_setting(
				'libreriatres_core',
				$field,
				array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => '',
				)
			);
		}

		// Interruptor de pagos online.
		register_setting(
			'libreriatres_core',
			'libreriatres_pagos_online',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
				'default'           => 'no',
			)
		);

		// Token de la Graph API de Instagram.
		register_setting(
			'libreriatres_core',
			'libreriatres_instagram_token',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'libreriatres_core',
			'libreriatres_instagram_version',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'v22.0',
			)
		);
	}

	/**
	 * Sanitiza un checkbox: "yes" o "no".
	 */
	public static function sanitize_checkbox( $value ) {
		return 'yes' === $value ? 'yes' : 'no';
	}

	/**
	 * Renderiza la pagina de ajustes.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$log    = get_option( 'libreriatres_core_log' );
		$error  = get_option( 'libreriatres_core_error' );
		$counts = array(
			'productos'  => wp_count_posts( 'product' )->publish,
			'categorias' => wp_count_terms( array( 'taxonomy' => 'product_cat' ) ),
			'paginas'    => wp_count_posts( 'page' )->publish,
		);
		?>
		<div class="wrap">
			<h1>Librería Tres</h1>

			<?php if ( $error ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
			<?php endif; ?>

			<?php if ( isset( $_GET['ig_ok'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo esc_html( sprintf( 'Instagram: se actualizaron %d publicaciones.', (int) $_GET['ig_ok'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?></p>
				</div>
			<?php endif; ?>

			<?php if ( isset( $_GET['ig_error'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html( 'Instagram: no se pudo actualizar el feed.', 'libreriatres-core' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $log ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo esc_html__( 'Configuración aplicada. Última ejecución:', 'libreriatres-core' ); ?> <?php echo esc_html( $log ); ?></p>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width:720px;padding:20px;margin-bottom:24px;">
				<h2><?php esc_html_e( 'Datos del negocio', 'libreriatres-core' ); ?></h2>
				<form method="post" action="options.php">
					<?php settings_fields( 'libreriatres_core' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="libreriatres_whatsapp"><?php esc_html_e( 'WhatsApp (con prefijo, solo dígitos)', 'libreriatres-core' ); ?></label></th>
							<td><input name="libreriatres_whatsapp" id="libreriatres_whatsapp" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'libreriatres_whatsapp' ) ); ?>"><p class="description"><?php esc_html_e( 'Ej: 5491178195270', 'libreriatres-core' ); ?></p></td>
						</tr>
						<tr>
							<th scope="row"><label for="libreriatres_phone"><?php esc_html_e( 'Teléfono visible', 'libreriatres-core' ); ?></label></th>
							<td><input name="libreriatres_phone" id="libreriatres_phone" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'libreriatres_phone' ) ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><label for="libreriatres_address"><?php esc_html_e( 'Dirección', 'libreriatres-core' ); ?></label></th>
							<td><input name="libreriatres_address" id="libreriatres_address" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'libreriatres_address' ) ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><label for="libreriatres_hours"><?php esc_html_e( 'Horarios', 'libreriatres-core' ); ?></label></th>
							<td><input name="libreriatres_hours" id="libreriatres_hours" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'libreriatres_hours' ) ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><label for="libreriatres_instagram"><?php esc_html_e( 'Instagram (usuario)', 'libreriatres-core' ); ?></label></th>
							<td><input name="libreriatres_instagram" id="libreriatres_instagram" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'libreriatres_instagram' ) ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Pagos online', 'libreriatres-core' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="libreriatres_pagos_online" id="libreriatres_pagos_online" value="yes" <?php checked( 'yes', get_option( 'libreriatres_pagos_online' ) ); ?>>
									<?php esc_html_e( 'Habilitar checkout con pasarela de pago (MercadoPago). Si está apagado, el pedido se cierra por WhatsApp.', 'libreriatres-core' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="libreriatres_instagram_token"><?php esc_html_e( 'Token de Instagram', 'libreriatres-core' ); ?></label></th>
							<td>
								<input name="libreriatres_instagram_token" id="libreriatres_instagram_token" type="text" class="regular-text" autocomplete="off" value="<?php echo esc_attr( get_option( 'libreriatres_instagram_token' ) ); ?>">
								<p class="description">
									<?php esc_html_e( 'Access token de la Graph API de Instagram (scope instagram_basic). Con esto la home muestra las últimas 4 publicaciones reales. Sin token se muestran los placeholders.', 'libreriatres-core' ); ?>
									<?php
									$libreriatres_error = Libreria_Tres_Core_Instagram::last_error();
									if ( $libreriatres_error && Libreria_Tres_Core_Instagram::get_token() ) {
										echo '<br><span style="color:#b32d2e">' . esc_html( $libreriatres_error ) . '</span>';
									}
									?>
								</p>
								<p>
									<?php
									$libreriatres_feed = Libreria_Tres_Core_Instagram::get_feed();
									printf(
										/* translators: %d: cantidad de publicaciones. */
										esc_html__( 'Publicaciones en el caché: %d', 'libreriatres-core' ),
										count( $libreriatres_feed )
									);
									?>
								</p>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
									<input type="hidden" name="action" value="libreriatres_instagram_test">
									<?php wp_nonce_field( 'libreriatres_instagram_test' ); ?>
									<?php submit_button( __( 'Probar conexión y refrescar feed', 'libreriatres-core' ), 'secondary', 'submit', false ); ?>
								</form>
							</td>
						</tr>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>

			<div class="card" style="max-width:720px;padding:20px;">
				<h2><?php esc_html_e( 'Estado de la tienda', 'libreriatres-core' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: 1: productos, 2: categorias, 3: paginas. */
						esc_html__( '%1$d productos · %2$d categorías · %3$d páginas', 'libreriatres-core' ),
						(int) $counts['productos'],
						(int) $counts['categorias'],
						(int) $counts['paginas']
					);
					?>
				</p>
				<p class="description"><?php esc_html_e( 'Reejecutar borra el contenido actual (productos, páginas, menú e imágenes) y lo vuelve a crear con los datos de ejemplo.', 'libreriatres-core' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="libreriatres_reseed">
					<?php wp_nonce_field( 'libreriatres_reseed' ); ?>
					<?php submit_button( __( 'Reejecutar configuración', 'libreriatres-core' ), 'secondary', 'submit', false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Procesa el reejecutar configuracion.
	 */
	public static function handle_reseed() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tenés permisos.', 'libreriatres-core' ) );
		}
		check_admin_referer( 'libreriatres_reseed' );

		delete_option( 'libreriatres_core_version' );
		Libreria_Tres_Core::bootstrap( true );

		wp_safe_redirect( admin_url( 'admin.php?page=libreriatres&reseed=1' ) );
		exit;
	}

	/**
	 * Prueba el token de Instagram y refresca el feed.
	 */
	public static function handle_instagram_test() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tenés permisos.', 'libreriatres-core' ) );
		}
		check_admin_referer( 'libreriatres_instagram_test' );

		$result = Libreria_Tres_Core_Instagram::test();

		if ( is_wp_error( $result ) ) {
			update_option( 'libreriatres_instagram_error', $result->get_error_message() );
			wp_safe_redirect( admin_url( 'admin.php?page=libreriatres&ig_error=1' ) );
			exit;
		}

		delete_option( 'libreriatres_instagram_error' );

		$feed = Libreria_Tres_Core_Instagram::refresh();

		if ( is_wp_error( $feed ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=libreriatres&ig_error=1' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=libreriatres&ig_ok=' . count( $feed ) ) );
		exit;
	}

	/**
	 * Shortcode de contacto: datos + formulario que deriva a WhatsApp.
	 */
	public static function contacto_shortcode() {
		$whatsapp = preg_replace( '/\D+/', '', (string) get_option( 'libreriatres_whatsapp', '5491178195270' ) );
		$phone    = (string) get_option( 'libreriatres_phone', '11 7819 5270' );
		$address  = (string) get_option( 'libreriatres_address', '9 de Abril, Esteban Echeverría' );
		$hours    = (string) get_option( 'libreriatres_hours', 'Lunes a Sábados 14 a 22 hs' );
		$insta    = (string) get_option( 'libreriatres_instagram', 'libreria.tres' );

		ob_start();
		?>
		<div class="contact-grid">
			<div class="contact-grid__info">
				<h3><?php esc_html_e( 'Escribinos', 'libreriatres-core' ); ?></h3>
				<ul class="contact-list">
					<li><strong><?php esc_html_e( 'WhatsApp', 'libreriatres-core' ); ?></strong>: <a href="https://wa.me/<?php echo esc_attr( $whatsapp ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $phone ); ?></a></li>
					<li><strong><?php esc_html_e( 'Dirección', 'libreriatres-core' ); ?></strong>: <?php echo esc_html( $address ); ?></li>
					<li><strong><?php esc_html_e( 'Horarios', 'libreriatres-core' ); ?></strong>: <?php echo esc_html( $hours ); ?></li>
					<li><strong>Instagram</strong>: <a href="https://www.instagram.com/<?php echo esc_attr( ltrim( $insta, '@' ) ); ?>/" target="_blank" rel="noopener">@<?php echo esc_html( $insta ); ?></a></li>
				</ul>
			</div>

			<form class="contact-form" data-wa-form data-whatsapp="<?php echo esc_attr( $whatsapp ); ?>">
				<label>
					<span><?php esc_html_e( 'Tu nombre', 'libreriatres-core' ); ?></span>
					<input type="text" name="nombre" required>
				</label>
				<label>
					<span><?php esc_html_e( 'Tu mensaje', 'libreriatres-core' ); ?></span>
					<textarea name="mensaje" rows="4" required></textarea>
				</label>
				<button type="submit" class="btn btn--whatsapp">
					<?php esc_html_e( 'Enviar por WhatsApp', 'libreriatres-core' ); ?>
				</button>
			</form>
		</div>
		<script>
		document.addEventListener('submit', function (e) {
			var form = e.target;
			if (!form.matches('[data-wa-form]')) return;
			e.preventDefault();
			var data = new FormData(form);
			var msg = '¡Hola! Soy ' + (data.get('nombre') || '') + '.\n\n' + (data.get('mensaje') || '');
			window.open('https://wa.me/' + form.getAttribute('data-whatsapp') + '?text=' + encodeURIComponent(msg), '_blank');
		});
		</script>
		<?php
		return ob_get_clean();
	}
}
