<?php
/**
 * Opciones del sitio y configuracion de WooCommerce.
 *
 * @package Libreria_Tres_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valores por defecto del negocio.
 */
class Libreria_Tres_Core_Options {

	/**
	 * Opciones que administra el plugin.
	 */
	public static function defaults() {
		$defaults = array(
			'libreriatres_whatsapp'        => '5491178195270',
			'libreriatres_phone'           => '11 7819 5270',
			'libreriatres_address'         => '9 de Abril, Esteban Echeverría',
			'libreriatres_hours'           => 'Lunes a Sábados 14 a 22 hs (verano con cita)',
			'libreriatres_instagram'       => 'libreria.tres',
			'libreriatres_instagram_shots' => array( 1, 2, 3, 4 ),
			// La compra se cierra por WhatsApp hasta conectar MercadoPago.
			'libreriatres_pagos_online'    => 'no',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key, false ) ) {
				add_option( $key, $value );
			}
		}

		update_option( 'blogname', 'Librería Tres' );
		update_option( 'blogdescription', 'Librería, papelería e impresiones' );
		update_option( 'timezone_string', 'America/Argentina/Buenos_Aires' );
		update_option( 'date_format', 'd/m/Y' );
		update_option( 'time_format', 'H:i' );
		update_option( 'start_of_week', 1 );
		update_option( 'WPLANG', 'es_AR' );
		update_option( 'blog_public', 1 );
		update_option( 'default_ping_status', 'closed' );
		update_option( 'default_comment_status', 'closed' );
	}

	/**
	 * Ajustes de WooCommerce.
	 */
	public static function woocommerce() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$options = array(
			// Moneda y precios.
			'woocommerce_currency'                 => 'ARS',
			'woocommerce_currency_pos'             => 'left_space',
			'woocommerce_price_thousand_sep'       => '.',
			'woocommerce_price_decimal_sep'        => ',',
			'woocommerce_price_num_decimals'       => '0',
			'woocommerce_weight_unit'              => 'kg',
			'woocommerce_dimension_unit'           => 'cm',

			// Pais.
			'woocommerce_default_country'          => 'AR:BA',
			'woocommerce_allowed_countries'        => 'all',

			// Tienda.
			'woocommerce_enable_guest_checkout'    => 'yes',
			'woocommerce_enable_checkout_login_reminder' => 'no',
			'woocommerce_enable_ajax_add_to_cart'  => 'yes',
			'woocommerce_cart_redirect_after_add'  => 'no',
			'woocommerce_enable_coupons'           => 'yes',
			'woocommerce_calc_taxes'               => 'no',
			'woocommerce_enable_reviews'           => 'yes',
			'woocommerce_manage_stock'             => 'no',
			'woocommerce_hide_out_of_stock_items'  => 'no',
			'woocommerce_stock_format'             => '',
			'woocommerce_ship_to_countries'        => 'all',
			'woocommerce_shipping_cost_requires_address' => 'no',
			'woocommerce_enable_shipping_calc'     => 'yes',
			'woocommerce_ship_to_destination'      => 'shipping',

			// Catalogos.
			'woocommerce_catalog_columns'          => '4',
			'woocommerce_catalog_rows'             => '3',

			// Quitar el modo "próximamente" y la tienda de prueba.
			'woocommerce_coming_soon'              => 'no',
			'woocommerce_store_pages_only'         => 'no',
			'woocommerce_demo_store'               => 'no',
			'woocommerce_allow_tracking'            => 'no',
			'woocommerce_merchant_email_notifications' => 'no',
		);

		foreach ( $options as $key => $value ) {
			update_option( $key, $value );
		}

		// Impuestos off pero con precios con simbolo.
		update_option( 'woocommerce_price_display_suffix', '' );

		// Notificaciones: desactivamos recordatorios automaticos.
		update_option( 'woocommerce_enable_reviews', 'yes' );
	}

	/**
	 * Devuelve el numero de WhatsApp en digitos.
	 */
	public static function whatsapp() {
		return preg_replace( '/\D+/', '', (string) get_option( 'libreriatres_whatsapp', '5491178195270' ) );
	}
}
