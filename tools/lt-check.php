<?php
/**
 * Chequeo de sintaxis PHP de archivos del sitio (temporal).
 * Uso:
 *   lt-check.php?token=SECRETO&path=...&from=1&to=60
 *   lt-check.php?token=SECRETO&path=...&mode=depth
 *   lt-check.php?token=SECRETO&path=...&mode=tokens&limit=120
 *   lt-check.php?token=SECRETO&path=...&mode=info
 *   lt-check.php?token=SECRETO&path=...&mode=hex&offset=0&length=64
 */

$token = 'lt-9f2c1d';
$base  = '/home/u352889191/domains/forestgreen-coyote-929041.hostingersite.com/public_html';

if ( ! isset( $_GET['token'] ) || $_GET['token'] !== $token ) {
	http_response_code( 403 );
	exit( 'no' );
}

$rel = isset( $_GET['path'] ) ? $_GET['path'] : '';
$abs = realpath( $base . '/' . ltrim( $rel, '/' ) );

if ( ! $abs || strpos( $abs, $base ) !== 0 || ! is_file( $abs ) ) {
	http_response_code( 404 );
	exit( 'not found' );
}

$src  = file_get_contents( $abs );
$mode = isset( $_GET['mode'] ) ? $_GET['mode'] : '';

if ( 'info' === $mode ) {
	echo 'bytes: ' . strlen( $src ) . "\n";
	echo 'md5: ' . md5( $src ) . "\n";
	echo 'sha1: ' . sha1( $src ) . "\n";
	$lines = explode( "\n", $src );
	echo 'lines: ' . count( $lines ) . "\n";
	exit;
}

if ( 'hex' === $mode ) {
	$offset = isset( $_GET['offset'] ) ? (int) $_GET['offset'] : 0;
	$length = isset( $_GET['length'] ) ? (int) $_GET['length'] : 64;
	$slice  = substr( $src, $offset, $length );
	echo bin2hex( $slice ) . "\n";
	echo '--- texto ---\n';
	echo $slice . "\n";
	echo '--- chars ---\n';
	foreach ( str_split( $slice ) as $ch ) {
		echo ord( $ch ) . ' ';
	}
	echo "\n";
	exit;
}

if ( 'tokens' === $mode ) {
	$tokens = token_get_all( $src );
	$limit  = isset( $_GET['limit'] ) ? (int) $_GET['limit'] : 120;
	$i      = 0;
	foreach ( $tokens as $t ) {
		if ( $i++ > $limit ) {
			break;
		}
		if ( is_array( $t ) ) {
			$text = str_replace( "\n", '\n', $t[1] );
			if ( strlen( $text ) > 60 ) {
				$text = substr( $text, 0, 60 ) . '...';
			}
			printf( "%4d L%-5d %-28s %s\n", $i, $t[2], $t[0], $text );
		} else {
			printf( "%4d L%-5s %-28s %s\n", $i, '-', 'CHAR', $t );
		}
	}
	exit;
}

if ( 'depth' === $mode ) {
	$tokens = token_get_all( $src );
	$depth  = 0;
	$line   = 1;
	foreach ( $tokens as $t ) {
		if ( is_array( $t ) ) {
			$line = $t[2];
			if ( '{' === $t[0] ) {
				$depth++;
			} elseif ( '}' === $t[0] ) {
				$depth--;
				if ( $depth < 0 ) {
					echo "BRACE NEGATIVO en linea $line\n";
					$depth = 0;
				}
			}
		}
	}
	echo "profundidad final: $depth\n";
	exit;
}

try {
	token_get_all( $src, TOKEN_PARSE );
	echo "PARSE OK\n";
} catch ( ParseError $e ) {
	echo 'PARSE ERROR: ' . $e->getMessage() . ' @ line ' . $e->getLine() . "\n";
}

$lines = explode( "\n", $src );
$from  = isset( $_GET['from'] ) ? max( 1, (int) $_GET['from'] ) : 1;
$to    = isset( $_GET['to'] ) ? min( count( $lines ), (int) $_GET['to'] ) : 60;

for ( $i = $from; $i <= $to; $i++ ) {
	printf( "%4d| %s\n", $i, rtrim( $lines[ $i - 1 ] ) );
}
