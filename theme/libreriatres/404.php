<?php
/**
 * 404.
 *
 * @package Libreria_Tres
 */

get_header();
?>

<div class="container page-wrap page-wrap--center">
	<div class="error-404">
		<span class="error-404__code">404</span>
		<h1 class="page-title"><?php esc_html_e( 'Ups, no encontramos esta página', 'libreriatres' ); ?></h1>
		<p><?php esc_html_e( 'Puede que el link haya cambiado. Volvé al inicio y seguí mirando.', 'libreriatres' ); ?></p>
		<div class="error-404__actions">
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Volver al inicio', 'libreriatres' ); ?></a>
			<?php
			if ( function_exists( 'wc_get_page_permalink' ) ) :
				echo libreriatres_whatsapp_link( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					__( 'Hola! No encuentro lo que estoy buscando.', 'libreriatres' )
				);
			endif;
			?>
		</div>
	</div>
</div>

<?php
get_footer();
