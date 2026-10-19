<?php
/**
 * Banner de pedido por WhatsApp.
 *
 * @package Libreria_Tres
 */

?>
<section class="section section--wa-banner">
	<div class="container">
		<div class="wa-banner">
			<span class="wa-banner__icon" aria-hidden="true">
				<?php echo libreriatres_icon( 'chat', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
			<div class="wa-banner__text">
				<h2 class="wa-banner__title"><?php esc_html_e( '¿Querés hacer un pedido?', 'libreriatres' ); ?></h2>
				<p class="wa-banner__sub"><?php esc_html_e( 'Escribinos por WhatsApp y te pasamos toda la info.', 'libreriatres' ); ?></p>
			</div>
			<div class="wa-banner__actions">
				<?php
				echo libreriatres_whatsapp_link( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					__( '¡Hola! Quiero hacer un pedido en Libreria Tres.', 'libreriatres' )
				);
				?>
			</div>
			<span class="wa-banner__deco" aria-hidden="true"></span>
		</div>
	</div>
</section>
