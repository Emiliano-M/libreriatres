<?php
/**
 * Pagina individual.
 *
 * @package Libreria_Tres
 */

get_header();

/**
 * Las paginas de WooCommerce (carrito, checkout, mi cuenta) necesitan el
 * ancho completo: el limite de lectura las deja muy angostas.
 */
$libreriatres_woo_page = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
?>

<div class="container page-wrap<?php echo $libreriatres_woo_page ? ' page-wrap--woo' : ''; ?>">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'single' ); ?>>
			<header class="single__head">
				<h1 class="page-title"><?php the_title(); ?></h1>
			</header>

			<div class="single__content entry-content<?php echo $libreriatres_woo_page ? ' single__content--wide' : ''; ?>">
				<?php
				the_content();

				wp_link_pages(
					array(
						'before' => '<nav class="page-links">',
						'after'  => '</nav>',
					)
				);
				?>
			</div>
		</article>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
		<?php
	endwhile;
	?>
</div>

<?php
get_footer();
