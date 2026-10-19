<?php
/**
 * Wrapper de las paginas de WooCommerce (shop, producto, carrito, checkout).
 *
 * @package Libreria_Tres
 */

get_header();
?>

<div class="container page-wrap page-wrap--woo">
	<?php woocommerce_output_all_notices(); ?>
	<?php woocommerce_content(); ?>
</div>

<?php
get_footer();
