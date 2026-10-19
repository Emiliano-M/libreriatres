<?php
/**
 * Plugin Name: Libreria Tres Debug
 * Description: Registra los errores fatales en wp-content/lt-error.log (temporal).
 */

@ini_set( 'display_errors', 1 );
@error_reporting( E_ALL );

register_shutdown_function(
	function () {
		$e = error_get_last();
		if ( $e && in_array( $e['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ), true ) ) {
			@file_put_contents(
				WP_CONTENT_DIR . '/lt-error.log',
				gmdate( 'c' ) . ' ' . wp_json_encode( $e ) . "\n",
				FILE_APPEND
			);
		}
	}
);
