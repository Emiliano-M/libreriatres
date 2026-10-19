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
				<?php esc_html_e( 'Útiles escolares, papelería e impresiones para compartir todos los días.', 'libreriatres' ); ?>
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
			$hero = get_theme_mod( 'libreriatres_hero_image', LIBRERIA_TRES_URI . '/assets/img/hero.svg' );
			?>
			<img src="<?php echo esc_url( $hero ); ?>" alt="<?php esc_attr_e( 'Cuadernos, lapices y utiles escolares', 'libreriatres' ); ?>" width="640" height="560" fetchpriority="high">
			<span class="hero__scribble hero__scribble--one" aria-hidden="true"></span>
			<span class="hero__scribble hero__scribble--two" aria-hidden="true"></span>
		</div>
	</div>
</section>
