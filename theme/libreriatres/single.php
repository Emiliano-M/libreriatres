<?php
/**
 * Entrada individual (post).
 *
 * @package Libreria_Tres
 */

get_header();
?>

<div class="container page-wrap">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'single' ); ?>>
			<header class="single__head">
				<h1 class="page-title"><?php the_title(); ?></h1>
				<time class="blog-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="single__thumb"><?php the_post_thumbnail( 'libreriatres-wide' ); ?></figure>
			<?php endif; ?>

			<div class="single__content entry-content">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</div>

<?php
get_footer();
