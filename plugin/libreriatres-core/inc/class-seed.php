<?php
/**
 * Seed: crea paginas, menu, categorias, productos y ajustes de la tienda.
 *
 * @package Libreria_Tres_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contenido inicial de la tienda.
 */
class Libreria_Tres_Core_Seed {

	/**
	 * Ejecuta el seed completo.
	 */
	public static function run() {
		$pages = self::pages();
		$ids   = self::create_pages( $pages );

		self::configure_woocommerce_pages( $ids );
		// Las categorias se crean con los productos: el menu las necesita.
		self::create_products();
		self::create_menus();
		self::configure_front( $ids );
	}

	/**
	 * Se asegura de que el tema "libreriatres" este activo.
	 *
	 * @return string Slug del tema activo.
	 */
	public static function ensure_theme() {
		$theme = wp_get_theme( 'libreriatres' );

		if ( ! $theme->exists() ) {
			return '';
		}

		if ( 'libreriatres' !== get_stylesheet() ) {
			switch_theme( 'libreriatres' );
		}

		return 'libreriatres';
	}

	/* ------------------------------------------------------------------ */
	/* Contenido de las paginas                                            */
	/* ------------------------------------------------------------------ */

	/**
	 * Devuelve la definicion de las paginas.
	 */
	private static function pages() {
		return array(
			'inicio'             => array(
				'title'   => 'Inicio',
				'content' => '<!-- wp:paragraph --><p>Todo lo que necesitás, en un solo lugar. Útiles escolares, papelería e impresiones para compartir todos los días.</p><!-- /wp:paragraph -->',
			),
			'impresiones'        => array(
				'title'   => 'Impresiones',
				'content' => '<!-- wp:paragraph --><p>En <strong>Librería Tres</strong> hacemos impresiones a medida para que lleves tus proyectos, tareas y tus ideas terminadas.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":3} --><h3>Fotocopias</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Blanco y negro — desde $120 por hoja</li><li>Color — desde $350 por hoja</li><li>Copias A4 y A3</li></ul><!-- /wp:list -->

<!-- wp:heading {"level":3} --><h3>Impresiones digitales</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Plotter para afiches y cartelería</li><li>Fotografías, diplomas y credenciales</li><li>Invitaciones, agendas y cuadernos a medida</li></ul><!-- /wp:list -->

<!-- wp:heading {"level":3} --><h3>Acabados</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Encuadernación: espiral, tasté y wire-o</li><li>Plastificado y laminado</li><li>Perforado y grapado</li><li>Stickers y sellos</li></ul><!-- /wp:list -->

<!-- wp:paragraph --><p>¿Necesitás algo puntual? <a href="#" data-wa-order>Consultanos por WhatsApp</a> y te pasamos el precio al instante.</p><!-- /wp:paragraph -->',
			),
			'ofertas'            => array(
				'title'   => 'Ofertas',
				'content' => '<!-- wp:paragraph --><p>Combos pensados para que vengas con todo resuelto. Consultá los combos por WhatsApp: se arman a medida según lo que necesitás.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":3} --><h3>Combo Escuela</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>1 cuaderno espiral A4 + 1 cuaderno A5</li><li>1 carpeta N°3</li><li>Lápices de colores y reslatadores</li></ul><!-- /wp:list -->

<!-- wp:heading {"level":3} --><h3>Combo útiles</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Fibras, resaltadores y sharpers</li><li>Blocks, plasticeras y repasadores</li></ul><!-- /wp:list -->

<!-- wp:paragraph --><p>Los precios de los combos pueden variar según el stock. <a href="#" data-wa-order>Escribinos</a> y te armamos el tuyo.</p><!-- /wp:paragraph -->',
			),
			'contacto'           => array(
				'title'   => 'Contacto',
				'content' => '<!-- wp:paragraph --><p>Encontranos en el local o escribinos por WhatsApp. Te respondemos de lunes a sábado.</p><!-- /wp:paragraph -->

<!-- wp:shortcode -->[libreriatres_contacto]<!-- /wp:shortcode -->',
			),
			'sobre-nosotros'     => array(
				'title'   => 'Sobre nosotros',
				'content' => '<!-- wp:paragraph --><p>Somos <strong>Librería Tres</strong>, un emprendimiento familiar que nació con mucho ganas de crecer y ofrecerte todo lo que necesitás en un solo lugar.</p><!-- /wp:paragraph -->

<!-- wp:list --><ul><li>Útiles escolares</li><li>Papelería</li><li>Impresiones</li><li>Y mucho más…</li></ul><!-- /wp:list -->

<!-- wp:paragraph --><p>Simple, femenina, colorida y cercana. Eso es lo que vas a encontrar en nuestro local y también en esta tienda online.</p><!-- /wp:paragraph -->',
			),
			'politica-privacidad' => array(
				'title'   => 'Política de privacidad',
				'content' => '<!-- wp:paragraph --><p>En Librería Tres cuidamos tus datos. Solo los usamos para gestionar pedidos, consultas y comunicaciones sobre nuestros productos.</p><!-- /wp:paragraph -->

<!-- wp:paragraph --><p>Podés pedir la eliminación de tus datos escribinos a nuestro WhatsApp.</p><!-- /wp:paragraph -->',
			),
			'terminos'           => array(
				'title'   => 'Términos y condiciones',
				'content' => '<!-- wp:paragraph --><p>Los precios son en pesos argentinos y pueden variar sin previo aviso. Los pedidos se confirman por WhatsApp o por checkout online. Envíos a todo el país.</p><!-- /wp:paragraph -->',
			),
		);
	}

	/* ------------------------------------------------------------------ */
	/* Creacion de paginas                                                 */
	/* ------------------------------------------------------------------ */

	/**
	 * Crea las paginas y guarda sus IDs.
	 *
	 * @param array $pages Definicion de las paginas.
	 * @return array IDs indexados por slug.
	 */
	private static function create_pages( $pages ) {
		$ids = array();

		foreach ( $pages as $slug => $page ) {
			$existing = get_page_by_path( $slug );

			if ( $existing ) {
				$ids[ $slug ] = $existing->ID;
				continue;
			}

			$page_id = wp_insert_post(
				array(
					'post_title'   => $page['title'],
					'post_name'    => $slug,
					'post_content' => $page['content'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_author'  => self::admin_id(),
				)
			);

			if ( $page_id && ! is_wp_error( $page_id ) ) {
				update_post_meta( $page_id, '_libreriatres_seeded', 1 );
				$ids[ $slug ] = (int) $page_id;
			}
		}

		update_option( 'libreriatres_pages', $ids );

		return $ids;
	}

	/**
	 * ID de un administrador del sitio.
	 */
	private static function admin_id() {
		$admins = get_users(
			array(
				'role'    => 'administrator',
				'number'  => 1,
				'fields'  => 'ID',
				'orderby' => 'ID',
			)
		);

		return ! empty( $admins ) ? (int) $admins[0] : 1;
	}

	/* ------------------------------------------------------------------ */
	/* Menus                                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * Crea el menu principal con sus submenus.
	 */
	private static function create_menus() {
		$menu_name = 'Principal';
		$menu      = wp_get_nav_menu_object( $menu_name );

		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $menu_name );
		} else {
			$menu_id = $menu->term_id;
			// Limpia los items previos para que el seed sea idempotente.
			$items = wp_get_nav_menu_items( $menu_id );
			foreach ( (array) $items as $item ) {
				wp_delete_post( $item->ID, true );
			}
		}

		if ( is_wp_error( $menu_id ) ) {
			return;
		}

		$pages    = (array) get_option( 'libreriatres_pages', array() );
		$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/productos/' );

		// Inicio.
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => 'Inicio',
				'menu-item-url'    => home_url( '/' ),
				'menu-item-status' => 'publish',
			)
		);

		// Productos (con categorias como submenu).
		$productos = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => 'Productos',
				'menu-item-url'    => $shop_url,
				'menu-item-status' => 'publish',
			)
		);

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'orderby'    => 'menu_order',
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => $term->name,
						'menu-item-object'    => 'product_cat',
						'menu-item-object-id' => $term->term_id,
						'menu-item-type'      => 'taxonomy',
						'menu-item-status'    => 'publish',
						'menu-item-parent-id' => $productos,
					)
				);
			}
		}

		// Paginas.
		$menu_pages = array(
			'impresiones' => 'Impresiones',
			'ofertas'     => 'Ofertas',
			'contacto'    => 'Contacto',
		);

		foreach ( $menu_pages as $slug => $title ) {
			if ( empty( $pages[ $slug ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $title,
					'menu-item-object'    => 'page',
					'menu-item-object-id' => (int) $pages[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
		}

		$locations            = (array) get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $menu_id;
		$locations['footer']  = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/* ------------------------------------------------------------------ */
	/* Categorias y productos                                              */
	/* ------------------------------------------------------------------ */

	/**
	 * Crea categorias y productos de ejemplo.
	 */
	private static function create_products() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$categories = array(
			'utiles-escolares' => array( 'Útiles escolares', 0 ),
			'papeleria'        => array( 'Papelería', 1 ),
			'impresiones'      => array( 'Impresiones', 2 ),
			'ofertas-y-combos' => array( 'Ofertas y combos', 3 ),
		);

		$term_ids = array();

		foreach ( $categories as $slug => $data ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );

			if ( ! $term ) {
				$result = wp_insert_term( $data[0], 'product_cat', array( 'slug' => $slug ) );
				if ( is_wp_error( $result ) ) {
					continue;
				}
				$term_id = (int) $result['term_id'];
			} else {
				$term_id = (int) $term->term_id;
			}

			update_term_meta( $term_id, 'order', $data[1] );
			$term_ids[ $slug ] = $term_id;
		}

		$products = array(
			array(
				'name'     => 'Cuaderno espiral A4',
				'price'    => '3200',
				'cat'      => 'utiles-escolares',
				'extra'    => 'ofertas-y-combos',
				'image'    => 'cuaderno-espiral-a4.png',
				'featured' => true,
				'short'    => 'Espiral metálico, 200 hojas, tapa dura. Ideal para el día a día escolar.',
			),
			array(
				'name'     => 'Carpeta N°3',
				'price'    => '4500',
				'cat'      => 'utiles-escolares',
				'image'    => 'carpeta-n3.png',
				'featured' => true,
				'short'    => 'Carpeta con solapa y elástico, tamaño A4. Con clips y cartucheras.',
			),
			array(
				'name'     => 'Lápices de colores',
				'price'    => '1800',
				'cat'      => 'utiles-escolares',
				'image'    => 'lapices-de-colores.png',
				'featured' => true,
				'short'    => 'Caja de 12 lápices de colores, mina gruesa y colores vibrantes.',
			),
			array(
				'name'     => 'Fibras x10',
				'price'    => '2700',
				'cat'      => 'papeleria',
				'image'    => 'fibras-x10.png',
				'featured' => true,
				'short'    => 'Set de 10 fibras de colores intensos, punta fina.',
			),
			array(
				'name'     => 'Resaltadores x6',
				'price'    => '2800',
				'cat'      => 'papeleria',
				'image'    => 'resaltadores-x6.png',
				'featured' => true,
				'short'    => 'Set de 6 resaltadores de punta cónica, colores pastel.',
			),
			array(
				'name'     => 'Mochila escolar',
				'price'    => '12500',
				'cat'      => 'utiles-escolares',
				'extra'    => 'ofertas-y-combos',
				'image'    => 'mochila-escolar.png',
				'featured' => true,
				'short'    => 'Mochila con compartimentos, bolsillo frontal y tirantes acolchados.',
			),
			array(
				'name'     => 'Cuaderno espiral A5',
				'price'    => '2500',
				'cat'      => 'utiles-escolares',
				'image'    => 'cuaderno-espiral-a5.png',
				'featured' => true,
				'short'    => 'Espiral, 100 hojas, tapa dura. Perfecto para clases y apuntes.',
			),
			array(
				'name'     => 'Resma de hojas A4',
				'price'    => '6000',
				'cat'      => 'papeleria',
				'extra'    => 'impresiones',
				'image'    => 'resma-hojas-a4.png',
				'featured' => true,
				'short'    => 'Resma de 500 hojas A4 de 75g. Blanco y sin rayas.',
			),
			array(
				'name'     => 'Set de resaltadores',
				'price'    => '2800',
				'cat'      => 'papeleria',
				'image'    => 'set-resaltadores.png',
				'featured' => false,
				'short'    => 'Set de 4 resaltadores en su estuche. Colores surtidos.',
			),
			array(
				'name'     => 'Papel contact tiza',
				'price'    => '2100',
				'cat'      => 'papeleria',
				'extra'    => 'impresiones',
				'image'    => 'papel-contact-tiza.png',
				'featured' => false,
				'short'    => 'Papel autoadhesivo de 45 cm de ancho. Perfecto para cartelería y decoración.',
			),
		);

		$order = 0;

		foreach ( $products as $data ) {
			$order++;

			$existing = get_page_by_title( $data['name'], OBJECT, 'product' );

			if ( $existing ) {
				wp_delete_post( $existing->ID, true );
			}

			$product = new WC_Product_Simple();

			$product->set_name( $data['name'] );
			$product->set_status( 'publish' );
			$product->set_catalog_visibility( 'visible' );
			$product->set_regular_price( $data['price'] );
			$product->set_price( $data['price'] );
			$product->set_short_description( $data['short'] );
			$product->set_description( $data['short'] );
			$product->set_sku( 'lt-' . sanitize_title( $data['name'] ) );
			$product->set_manage_stock( false );
			$product->set_stock_status( 'instock' );
			$product->set_menu_order( $order );
			$product->set_reviews_allowed( true );
			$product->set_virtual( false );

			if ( ! empty( $data['featured'] ) ) {
				$product->set_featured( true );
			}

			$product_id = $product->save();

			if ( ! $product_id ) {
				continue;
			}

			update_post_meta( $product_id, '_libreriatres_seeded', 1 );

			$cat_ids = array();

			if ( ! empty( $term_ids[ $data['cat'] ] ) ) {
				$cat_ids[] = (int) $term_ids[ $data['cat'] ];
			}
			if ( ! empty( $data['extra'] ) && ! empty( $term_ids[ $data['extra'] ] ) ) {
				$cat_ids[] = (int) $term_ids[ $data['extra'] ];
			}

			if ( $cat_ids ) {
				wp_set_object_terms( $product_id, $cat_ids, 'product_cat' );
			}

			$attachment_id = Libreria_Tres_Core_Media::sideload( $data['image'], $data['name'] );

			if ( $attachment_id ) {
				set_post_thumbnail( $product_id, $attachment_id );
			}
		}
	}

	/* ------------------------------------------------------------------ */
	/* Portada y paginas de WooCommerce                                    */
	/* ------------------------------------------------------------------ */

	/**
	 * Define la pagina de inicio.
	 *
	 * @param array $ids IDs de las paginas indexados por slug.
	 */
	private static function configure_front( $ids ) {
		if ( ! empty( $ids['inicio'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', (int) $ids['inicio'] );
		}
	}

	/**
	 * Crea / asigna las paginas de WooCommerce.
	 *
	 * @param array $ids IDs de las paginas indexados por slug.
	 */
	private static function configure_woocommerce_pages( $ids ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$wc_pages = array(
			'productos' => array(
				'title'   => 'Productos',
				'slug'    => 'productos',
				'opt'     => 'woocommerce_shop_page_id',
				'content' => '',
			),
			'carrito'   => array(
				'title'   => 'Carrito',
				'slug'    => 'carrito',
				'opt'     => 'woocommerce_cart_page_id',
				'content' => '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->',
			),
			'checkout'  => array(
				'title'   => 'Checkout',
				'slug'    => 'checkout',
				'opt'     => 'woocommerce_checkout_page_id',
				'content' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->',
			),
			'mi-cuenta' => array(
				'title'   => 'Mi cuenta',
				'slug'    => 'mi-cuenta',
				'opt'     => 'woocommerce_myaccount_page_id',
				'content' => '<!-- wp:shortcode -->[woocommerce_my_account]<!-- /wp:shortcode -->',
			),
		);

		foreach ( $wc_pages as $key => $data ) {
			$existing = get_page_by_path( $data['slug'] );

			if ( $existing ) {
				update_option( $data['opt'], (int) $existing->ID );

				if ( $data['content'] && $existing->post_content !== $data['content'] ) {
					wp_update_post(
						array(
							'ID'           => $existing->ID,
							'post_content' => $data['content'],
						)
					);
				}
				continue;
			}

			$page_id = wp_insert_post(
				array(
					'post_title'   => $data['title'],
					'post_name'    => $data['slug'],
					'post_content' => $data['content'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_author'  => self::admin_id(),
				)
			);

			if ( $page_id && ! is_wp_error( $page_id ) ) {
				update_post_meta( $page_id, '_libreriatres_seeded', 1 );
				update_option( $data['opt'], (int) $page_id );
			}
		}

		// Paginas legales.
		if ( ! empty( $ids['politica-privacidad'] ) ) {
			update_option( 'wp_page_for_privacy_policy', (int) $ids['politica-privacidad'] );
		}
		if ( ! empty( $ids['terminos'] ) ) {
			update_option( 'woocommerce_terms_page_id', (int) $ids['terminos'] );
		}

		// WooCommerce necesita el slug de checkout en el objeto.
		if ( class_exists( 'WC' ) && WC()->query ) {
			WC()->query->init_query_vars();
		}
	}
}
