<?php
/**
 * Bloque de Instagram.
 *
 * Si el plugin tiene el token de la Graph API, muestra las ultimas
 * publicaciones reales (enlazando a cada post). Si no, cae en los
 * placeholders.
 *
 * @package Libreria_Tres
 */

$libreriatres_feed = array();

if ( class_exists( 'Libreria_Tres_Core_Instagram' ) ) {
	$libreriatres_feed = Libreria_Tres_Core_Instagram::get_feed();
}

$libreriatres_shots = get_option( 'libreriatres_instagram_shots', array() );
if ( empty( $libreriatres_shots ) || ! is_array( $libreriatres_shots ) ) {
	$libreriatres_shots = array( 1, 2, 3, 4 );
}
?>
<section class="section section--instagram">
	<div class="container insta">
		<div class="insta__content">
			<h2 class="insta__title"><?php esc_html_e( '¡Seguinos en Instagram!', 'libreriatres' ); ?></h2>
			<p class="insta__text"><?php esc_html_e( 'Enterate de todas las novedades, promos y más.', 'libreriatres' ); ?></p>
			<a class="insta__handle" href="<?php echo esc_url( libreriatres_instagram_link() ); ?>" target="_blank" rel="noopener">
				<?php echo libreriatres_icon( 'instagram', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				@<?php echo esc_html( libreriatres_instagram() ); ?>
			</a>
		</div>

		<div class="insta__gallery">
			<?php if ( ! empty( $libreriatres_feed ) ) : ?>
				<?php foreach ( $libreriatres_feed as $libreriatres_post ) : ?>
					<a class="insta__item" href="<?php echo esc_url( $libreriatres_post['permalink'] ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Ver publicación en Instagram', 'libreriatres' ); ?>">
						<img src="<?php echo esc_url( $libreriatres_post['image'] ); ?>" alt="<?php echo esc_attr( $libreriatres_post['caption'] ); ?>" width="200" height="250" loading="lazy">
					</a>
				<?php endforeach; ?>
			<?php else : ?>
				<?php foreach ( $libreriatres_shots as $libreriatres_i ) : ?>
					<a class="insta__item" href="<?php echo esc_url( libreriatres_instagram_link() ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Ver publicaciones en Instagram', 'libreriatres' ); ?>">
						<img src="<?php echo esc_url( LIBRERIA_TRES_URI . '/assets/img/insta-' . (int) $libreriatres_i . '.svg' ); ?>" alt="" width="200" height="260" loading="lazy">
					</a>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
