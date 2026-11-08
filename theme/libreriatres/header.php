<?php
/**
 * Header del sitio: topbar, logo, buscador, iconos y menu principal.
 *
 * @package Libreria_Tres
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Ir al contenido', 'libreriatres' ); ?></a>

<div class="site-wrap">

	<div class="topbar">
		<div class="container topbar__inner">
			<span class="topbar__item">
				<?php echo libreriatres_icon( 'pin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo esc_html( libreriatres_address() ); ?>
			</span>
			<span class="topbar__item topbar__item--hide-mobile">
				<?php echo libreriatres_icon( 'truck', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php esc_html_e( 'Envíos', 'libreriatres' ); ?>
			</span>
			<span class="topbar__item topbar__item--hide-mobile" style="display: none;">
				<?php echo libreriatres_icon( 'clock', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo esc_html( libreriatres_hours() ); ?>
			</span>
		</div>
	</div>

	<header class="site-header" id="siteHeader">
		<div class="container site-header__inner">
			<button type="button" class="header-icon header-icon--menu" data-menu-open aria-label="<?php esc_attr_e( 'Abrir menu', 'libreriatres' ); ?>" aria-controls="mobileMenu" aria-expanded="false">
				<?php echo libreriatres_icon( 'menu', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>

			<?php echo libreriatres_logo( 'logo logo--header' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<form role="search" method="get" class="header-search" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-live-search>
				<label class="screen-reader-text" for="header-search-field"><?php esc_html_e( 'Buscar productos', 'libreriatres' ); ?></label>
				<?php echo libreriatres_icon( 'search', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<input type="search" id="header-search-field" class="header-search__input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( '¿Qué estás buscando?', 'libreriatres' ); ?>" autocomplete="off">
				<button type="button" class="header-search__clear" aria-label="<?php esc_attr_e( 'Limpiar búsqueda', 'libreriatres' ); ?>" hidden>
					<?php echo libreriatres_icon( 'close', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<span class="header-search__spinner" aria-hidden="true"></span>
				<?php if ( function_exists( 'wc_get_page_id' ) ) : ?>
					<input type="hidden" name="post_type" value="product">
				<?php endif; ?>
				<div class="header-search__results" role="listbox" aria-label="<?php esc_attr_e( 'Resultados de búsqueda', 'libreriatres' ); ?>" hidden></div>
			</form>

			<div class="header-actions">
				<button type="button" class="header-icon header-icon--search" data-search-toggle data-target="headerSearchMobile" aria-label="<?php esc_attr_e( 'Buscar productos', 'libreriatres' ); ?>" aria-controls="headerSearchMobile" aria-expanded="false">
					<?php echo libreriatres_icon( 'search', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>

				<?php echo libreriatres_whatsapp_icon_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<?php if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'myaccount' ) > 0 ) : ?>
					<a class="icon-link icon-link--user" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'Mi cuenta', 'libreriatres' ); ?>">
						<?php echo libreriatres_icon( 'user', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>

				<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
					<a class="icon-link icon-link--cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Carrito', 'libreriatres' ); ?>">
						<?php echo libreriatres_icon( 'cart', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo libreriatres_header_cart_count(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="header-search-mobile" id="headerSearchMobile" hidden>
			<div class="container">
				<form role="search" method="get" class="header-search header-search--mobile" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-live-search>
					<label class="screen-reader-text" for="mobile-header-search-field"><?php esc_html_e( 'Buscar productos', 'libreriatres' ); ?></label>
					<?php echo libreriatres_icon( 'search', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<input type="search" id="mobile-header-search-field" class="header-search__input" name="s" placeholder="<?php esc_attr_e( '¿Qué estás buscando?', 'libreriatres' ); ?>" autocomplete="off">
					<button type="button" class="header-search__clear" aria-label="<?php esc_attr_e( 'Limpiar búsqueda', 'libreriatres' ); ?>" hidden>
						<?php echo libreriatres_icon( 'close', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
					<span class="header-search__spinner" aria-hidden="true"></span>
					<?php if ( function_exists( 'wc_get_page_id' ) ) : ?>
						<input type="hidden" name="post_type" value="product">
					<?php endif; ?>
					<div class="header-search__results" role="listbox" aria-label="<?php esc_attr_e( 'Resultados de búsqueda', 'libreriatres' ); ?>" hidden></div>
				</form>
			</div>
		</div>

		<nav class="main-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'libreriatres' ); ?>">
			<div class="container">
				<?php
				if ( has_nav_menu( 'primary' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'main-nav__list',
							'depth'          => 0,
						)
					);
				} else {
					wp_nav_menu(
						array(
							'container'  => false,
							'menu_class' => 'main-nav__list',
							'depth'      => 0,
						)
					);
				}
				?>
			</div>
		</nav>
	</header>

	<main id="main" class="site-main">
