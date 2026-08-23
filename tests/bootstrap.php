<?php
/**
 * PHPUnit bootstrap.
 *
 * The only WordPress functions this library calls are the two it uses to read
 * the header safely, stubbed here with core's behaviour.
 *
 * @package ArrayPress\UserAgentUtils
 */

declare( strict_types=1 );

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	function wp_strip_all_tags( $string, $remove_breaks = false ) {
		$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $string );
		$string = strip_tags( $string );

		return trim( $string );
	}
}
