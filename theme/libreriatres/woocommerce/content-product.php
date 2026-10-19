<?php
/**
 * Tarjeta de producto (loop de WooCommerce).
 *
 * @package Libreria_Tres
 */

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$libreriatres_link  = $product->get_permalink();
$libreriatres_name  = $product->get_name();
$libreriatres_price = $product->get_price_html();

$libreriatres_msg = sprintf(
	/* translators: %s: nombre del producto. */
	__( '¡Hola! Consulto por el producto "%s" (%s). ¿Me contás disponibilidad y precio?', 'libreriatres' ),
	$libreriatres_name,
	html_entity_decode( wp_strip_all_tags( $libreriatres_price ), ENT_QUOTES, 'UTF-8' )
);
?>
<div <?php wc_product_class( 'product-card', $product ); ?>>
	<a class="product-card__media" href="<?php echo esc_url( $libreriatres_link ); ?>">
		<?php
		echo woocommerce_get_product_thumbnail(
			'libreriatres-card',
			array(
				'loading'  => 'lazy',
				'alt'      => esc_attr( $libreriatres_name ),
				'decoding' => 'async',
			)
		);
		?>
		<button type="button" class="product-card__wish" aria-label="<?php esc_attr_e( 'Agregar a favoritos', 'libreriatres' ); ?>" data-wish>
			<?php echo libreriatres_icon( 'heart', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
		<?php if ( $product->is_on_sale() ) : ?>
			<span class="product-card__badge"><?php esc_html_e( 'Oferta', 'libreriatres' ); ?></span>
		<?php endif; ?>
	</a>

	<div class="product-card__body">
		<h3 class="product-card__title"><a href="<?php echo esc_url( $libreriatres_link ); ?>"><?php echo esc_html( $libreriatres_name ); ?></a></h3>

		<div class="product-card__meta">
			<?php
			$libreriatres_terms = get_the_terms( $product->get_id(), 'product_cat' );
			if ( $libreriatres_terms && ! is_wp_error( $libreriatres_terms ) ) :
				$libreriatres_term = $libreriatres_terms[0];
				?>
				<a class="product-card__cat" href="<?php echo esc_url( get_term_link( $libreriatres_term ) ); ?>"><?php echo esc_html( $libreriatres_term->name ); ?></a>
			<?php endif; ?>
		</div>

		<div class="product-card__price"><?php echo wp_kses_post( $libreriatres_price ); ?></div>

		<div class="product-card__actions">
			<?php
			echo libreriatres_whatsapp_link( $libreriatres_msg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>

			<?php
			$libreriatres_in_cart = false;
			if ( function_exists( 'WC' ) && WC()->cart ) {
				$libreriatres_in_cart = WC()->cart->find_product_in_cart( $product->get_id() );
			}
			?>
			<form class="product-card__cart<?php echo $libreriatres_in_cart ? ' is-hidden' : ''; ?>" method="post" enctype="multipart/form-data" action="<?php echo esc_url( $product->get_permalink() ); ?>" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
				<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
				<button type="submit" class="btn btn--cart">
					<span class="btn__icon" aria-hidden="true"><?php echo libreriatres_icon( 'cart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="btn__label"><?php esc_html_e( 'Agregar al carrito', 'libreriatres' ); ?></span>
				</button>
			</form>

			<a class="btn btn--cart btn--cart--added product-card__in-cart<?php echo $libreriatres_in_cart ? '' : ' is-hidden'; ?>" href="<?php echo esc_url( wc_get_cart_url() ); ?>" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
				<span class="btn__icon" aria-hidden="true"><?php echo libreriatres_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="btn__label"><?php esc_html_e( 'En el carrito', 'libreriatres' ); ?></span>
			</a>
		</div>
	</div>
</div>
