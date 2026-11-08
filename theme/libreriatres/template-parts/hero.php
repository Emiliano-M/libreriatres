<?php
/**
 * Hero de la home.
 *
 * @package Libreria_Tres
 */

?>
<section class="hero">
	<div class="container hero__inner">
		<div class="hero__content">
			<h1 class="hero__title">
				<?php esc_html_e( 'Todo lo que necesitás, en un solo lugar', 'libreriatres' ); ?>
				<span class="hero__heart" aria-hidden="true">&#9825;</span>
			</h1>
			<p class="hero__text">
				<?php esc_html_e( 'Tu aliado para estudiar, trabajar y crear, con productos de librería, útiles escolares, papelería e impresiones para cada proyecto.', 'libreriatres' ); ?>
			</p>
			<div class="hero__cta">
				<?php
				echo libreriatres_whatsapp_link( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					__( '¡Hola! Estoy mirando la web de Libreria Tres y quiero consultar por productos.', 'libreriatres' )
				);
				?>
			</div>
			<ul class="hero__chips">
				<li><?php esc_html_e( 'Envíos a todo el país', 'libreriatres' ); ?></li>
				<li><?php esc_html_e( 'Pickup en el local', 'libreriatres' ); ?></li>
				<li><?php esc_html_e( 'Impresiones a medida', 'libreriatres' ); ?></li>
			</ul>
		</div>

		<div class="hero__media">
			<?php
			// La foto vive en el tema. Un theme_mod viejo (o el svg de respaldo)
			// hacia que subir hero.png y deployar no cambiara la imagen.
			$hero_file = LIBRERIA_TRES_DIR . '/assets/img/hero.png';
			$hero      = LIBRERIA_TRES_URI . '/assets/img/hero.png';
			if ( ! file_exists( $hero_file ) ) {
				$hero_file = LIBRERIA_TRES_DIR . '/assets/img/hero.png';
				$hero      = LIBRERIA_TRES_URI . '/assets/img/hero.png';
			}
			if ( file_exists( $hero_file ) ) {
				$hero = add_query_arg( 'v', (string) filemtime( $hero_file ), $hero );
			}
			?>
			<img src="<?php echo esc_url( $hero ); ?>" alt="<?php esc_attr_e( 'Cuadernos, lapices y utiles escolares', 'libreriatres' ); ?>" width="1200" height="800" fetchpriority="high">

		</div>
	</div>
</section>
