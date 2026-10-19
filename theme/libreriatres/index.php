<?php
/**
 * Plantilla por defecto (blog / fallback).
 *
 * @package Libreria_Tres
 */

get_header();
?>

<div class="container page-wrap">
	<?php if ( have_posts() ) : ?>
		<header class="page-head">
			<h1 class="page-title"><?php single_post_title(); ?></h1>
		</header>

		<div class="blog-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'blog-card' ); ?>>
					<a class="blog-card__thumb" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'libreriatres-card' ); ?>
						<?php else : ?>
							<span class="blog-card__placeholder" aria-hidden="true"></span>
						<?php endif; ?>
					</a>
					<h2 class="blog-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
					<time class="blog-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				</article>
				<?php
			endwhile;
			?>
		</div>

		<?php libreriatres_pagination(); ?>
	<?php else : ?>
		<p class="no-results"><?php esc_html_e( 'No encontramos nada. Probá con otra búsqueda.', 'libreriatres' ); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
