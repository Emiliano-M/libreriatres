<?php
/**
 * Bloque de productos destacados.
 *
 * @package Libreria_Tres
 */

$libreriatres_args = array(
	'post_type'           => 'product',
	'post_status'         => 'publish',
	'posts_per_page'      => 8,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
	'tax_query'           => array(
		array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => 'featured',
			'operator' => 'IN',
		),
	),
);

if ( ! wc_get_loop_prop( 'loop' ) ) {
	$libreriatres_query = new WP_Query( $libreriatres_args );
} else {
	$libreriatres_query = $GLOBALS['wp_query'];
}

if ( ! $libreriatres_query->have_posts() ) {
	$libreriatres_args['tax_query']                = array();
	$libreriatres_args['tax_query'][]              = array(
		'taxonomy' => 'product_cat',
		'field'    => 'slug',
		'terms'    => 'destacados',
		'operator' => 'IN',
	);
	$libreriatres_args['posts_per_page']           = 8;
	$libreriatres_query                             = new WP_Query( $libreriatres_args );
}

if ( ! $libreriatres_query->have_posts() ) {
	// Fallback: los productos mas recientes.
	unset( $libreriatres_args['tax_query'] );
	$libreriatres_query = new WP_Query( $libreriatres_args );
}

if ( ! $libreriatres_query->have_posts() ) {
	return;
}

$libreriatres_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/productos/' );
?>
<section class="section section--featured">
	<div class="container">
		<header class="section__head">
			<h2 class="section__title">
				<?php esc_html_e( 'Productos destacados', 'libreriatres' ); ?>
				<span class="section__heart" aria-hidden="true">&#9825;</span>
			</h2>
			<a class="section__more" href="<?php echo esc_url( $libreriatres_shop_url ); ?>">
				<?php esc_html_e( 'Ver todos', 'libreriatres' ); ?>
				<?php echo libreriatres_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</header>

		<div class="products-grid products-grid--scroll">
			<?php
			while ( $libreriatres_query->have_posts() ) :
				$libreriatres_query->the_post();
				wc_get_template_part( 'content', 'product' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
