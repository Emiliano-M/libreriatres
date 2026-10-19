<?php
/**
 * Reglas de venta de la tienda: por ahora la compra se cierra por WhatsApp.
 *
 * @package Libreria_Tres_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configuracion comercial.
 */
class Libreria_Tres_Core_Commerce {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_available_payment_gateways', array( __CLASS__, 'payment_gateways' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_checkout' ), 20 );
		add_filter( 'woocommerce_get_privacy_policy_text', '__return_empty_string' );
	}

	/**
	 * No hay pasarela de pago: la compra se coordina por WhatsApp.
	 *
	 * Cuando se conecte MercadoPago, sacar este filtro (o activar la
	 * opcion "libreriatres_pagos_online") para habilitar el checkout online.
	 */
	public static function payment_gateways( $gateways ) {
		if ( get_option( 'libreriatres_pagos_online' ) ) {
			return $gateways;
		}

		return array();
	}

	/**
	 * Manda del checkout al carrito: no hay pago online.
	 */
	public static function redirect_checkout() {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}

		// La pagina de "gracias por tu compra" se deja pasar.
		if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
			return;
		}

		// El carrito tambien es parte del checkout en WooCommerce: no se redirige.
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			return;
		}

		if ( ! function_exists( 'wc_get_cart_url' ) ) {
			return;
		}

		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}
}
