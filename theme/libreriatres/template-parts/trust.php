<?php
/**
 * Badges de confianza.
 *
 * @package Libreria_Tres
 */

$libreriatres_items = array(
	array(
		'icon'  => 'heart',
		'title' => __( 'Atención', 'libreriatres' ),
		'text'  => __( 'Te asesoramos como en el local', 'libreriatres' ),
	),
	array(
		'icon'  => 'shield',
		'title' => __( 'Productos de calidad', 'libreriatres' ),
		'text'  => __( 'Marcas y marcas registradas', 'libreriatres' ),
	),
	array(
		'icon'  => 'truck',
		'title' => __( 'Pagos y envíos seguros', 'libreriatres' ),
		'text'  => __( 'Envíos a todo el país', 'libreriatres' ),
	),
);
?>
<section class="section section--trust">
	<div class="container">
		<ul class="trust-grid">
			<?php foreach ( $libreriatres_items as $libreriatres_item ) : ?>
				<li class="trust-grid__item">
					<span class="trust-grid__icon">
						<?php echo libreriatres_icon( $libreriatres_item['icon'], 26 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<div class="trust-grid__text">
						<strong class="trust-grid__title"><?php echo esc_html( $libreriatres_item['title'] ); ?></strong>
						<span class="trust-grid__sub"><?php echo esc_html( $libreriatres_item['text'] ); ?></span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
