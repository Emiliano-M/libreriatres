<?php
/**
 * Círculos de categorías fijas de la home.
 *
 * Mantiene fijas las 4 categorías principales del diseño original:
 *  1. Útiles escolares (cuaderno espiral)
 *  2. Papelería (hoja/documento)
 *  3. Impresiones (impresora)
 *  4. Ofertas y combos (regalo)
 *
 * @package Libreria_Tres
 */

$libreriatres_fixed_cats = array(
	array(
		'slug'  => 'utiles-escolares',
		'name'  => __( 'Útiles escolares', 'libreriatres' ),
		'icon'  => 'notebook',
		'color' => 'cats-grid__item--c1',
	),
	array(
		'slug'  => 'papeleria',
		'name'  => __( 'Papelería', 'libreriatres' ),
		'icon'  => 'file',
		'color' => 'cats-grid__item--c2',
	),
	array(
		'slug'  => 'impresiones',
		'name'  => __( 'Impresiones', 'libreriatres' ),
		'icon'  => 'printer',
		'color' => 'cats-grid__item--c3',
	),
	array(
		'slug'  => 'ofertas-y-combos',
		'name'  => __( 'Ofertas y combos', 'libreriatres' ),
		'icon'  => 'gift',
		'color' => 'cats-grid__item--c4',
	),
);
?>
<section class="section section--categories">
	<div class="container">
		<ul class="cats-grid">
			<?php foreach ( $libreriatres_fixed_cats as $libreriatres_cat ) : ?>
				<?php
				$libreriatres_term = get_term_by( 'slug', $libreriatres_cat['slug'], 'product_cat' );
				if ( ! $libreriatres_term || is_wp_error( $libreriatres_term ) ) {
					$libreriatres_term = get_term_by( 'name', $libreriatres_cat['name'], 'product_cat' );
				}

				$libreriatres_url = ( $libreriatres_term && ! is_wp_error( $libreriatres_term ) )
					? get_term_link( $libreriatres_term )
					: home_url( '/categoria-producto/' . $libreriatres_cat['slug'] . '/' );
				?>
				<li class="cats-grid__item <?php echo esc_attr( $libreriatres_cat['color'] ); ?>">
					<a class="cats-grid__link" href="<?php echo esc_url( $libreriatres_url ); ?>">
						<span class="cats-grid__icon">
							<?php echo libreriatres_icon( $libreriatres_cat['icon'], 44 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<span class="cats-grid__name"><?php echo esc_html( $libreriatres_cat['name'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
