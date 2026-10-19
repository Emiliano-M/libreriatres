<?php
/**
 * Circulos de categorias.
 *
 * @package Libreria_Tres
 */

// Excluimos la categoria por defecto de WooCommerce ("Sin categorizar").
$libreriatres_exclude = array( (int) get_option( 'libreriatres_default_cat' ) );

$libreriatres_uncat = get_term_by( 'slug', 'uncategorized', 'product_cat' );
if ( $libreriatres_uncat ) {
	$libreriatres_exclude[] = (int) $libreriatres_uncat->term_id;
}

// WooCommerce guarda el id de la categoria por defecto en esta opcion.
$libreriatres_default = (int) get_option( 'default_product_cat' );
if ( $libreriatres_default ) {
	$libreriatres_exclude[] = $libreriatres_default;
}

$libreriatres_cats = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'parent'     => 0,
		'exclude'    => array_filter( $libreriatres_exclude ),
		'number'     => 4,
		'orderby'    => 'menu_order',
	)
);

if ( is_wp_error( $libreriatres_cats ) || empty( $libreriatres_cats ) ) {
	return;
}

// Ordenamos por el meta 'order' que define WooCommerce para las categorias.
usort(
	$libreriatres_cats,
	function ( $a, $b ) {
		$order_a = (int) get_term_meta( $a->term_id, 'order', true );
		$order_b = (int) get_term_meta( $b->term_id, 'order', true );

		if ( $order_a === $order_b ) {
			return strcmp( $a->name, $b->name );
		}

		return $order_a <=> $order_b;
	}
);

$libreriatres_icons = array( 'notebook', 'file', 'printer', 'gift' );
?>
<section class="section section--categories">
	<div class="container">
		<ul class="cats-grid">
			<?php foreach ( $libreriatres_cats as $index => $libreriatres_cat ) : ?>
				<?php
				$libreriatres_icon_name = $libreriatres_icons[ $index % count( $libreriatres_icons ) ];
				$libreriatres_color    = 'cats-grid__item--c' . ( ( $index % 4 ) + 1 );
				?>
				<li class="cats-grid__item <?php echo esc_attr( $libreriatres_color ); ?>">
					<a class="cats-grid__link" href="<?php echo esc_url( get_term_link( $libreriatres_cat ) ); ?>">
						<span class="cats-grid__icon">
							<?php echo libreriatres_icon( $libreriatres_icon_name, 34 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<span class="cats-grid__name"><?php echo esc_html( $libreriatres_cat->name ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
