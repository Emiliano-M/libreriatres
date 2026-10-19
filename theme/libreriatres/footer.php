<?php
/**
 * Footer del sitio.
 *
 * @package Libreria_Tres
 */

?>
	</main><!-- #main -->

	<footer class="site-footer">
		<div class="container site-footer__grid">
			<div class="site-footer__brand">
				<?php echo libreriatres_logo( 'logo logo--footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<p class="site-footer__about">
					<?php esc_html_e( 'Todo lo que necesitás, en un solo lugar. Librería, papelería e impresiones para todos los días.', 'libreriatres' ); ?>
				</p>
			</div>

			<div class="site-footer__col">
				<h3 class="site-footer__title"><?php esc_html_e( 'Contacto', 'libreriatres' ); ?></h3>
				<ul class="site-footer__list">
					<li>
						<?php echo libreriatres_icon( 'chat', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a href="https://wa.me/<?php echo esc_attr( libreriatres_whatsapp_number() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( libreriatres_phone_display() ); ?></a>
					</li>
					<li>
						<?php echo libreriatres_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( libreriatres_address() ); ?></span>
					</li>
					<li>
						<?php echo libreriatres_icon( 'clock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( libreriatres_hours() ); ?></span>
					</li>
				</ul>
			</div>

			<div class="site-footer__col">
				<h3 class="site-footer__title"><?php esc_html_e( 'Seguinos', 'libreriatres' ); ?></h3>
				<ul class="site-footer__list">
					<li>
						<?php echo libreriatres_icon( 'instagram', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a href="<?php echo esc_url( libreriatres_instagram_link() ); ?>" target="_blank" rel="noopener">@<?php echo esc_html( libreriatres_instagram() ); ?></a>
					</li>
				</ul>
				<?php echo libreriatres_whatsapp_link( __( 'Hola! Quiero hacer una consulta.', 'libreriatres' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>

		<div class="site-footer__bottom">
			<div class="container">
				<p>
					<?php
					printf(
						/* translators: %s: anio actual. */
						esc_html__( '© %s Libreria Tres. Todos los derechos reservados.', 'libreriatres' ),
						esc_html( gmdate( 'Y' ) )
					);
					?>
				</p>
			</div>
		</div>
	</footer>

	<a class="wa-float" data-wa-float href="https://wa.me/" aria-label="<?php esc_attr_e( 'Consultanos por WhatsApp', 'libreriatres' ); ?>">
		<?php echo libreriatres_icon( 'whatsapp', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>

</div><!-- .site-wrap -->

<?php libreriatres_mobile_menu(); ?>

<?php wp_footer(); ?>
</body>
</html>
