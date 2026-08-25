<?php
/**
 * PHPUnit bootstrap.
 *
 * @package ArrayPress\UserAgentUtils
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! function_exists( 'wp_unslash' ) ) {
	/**
	 * Core strips the slashes it added to the superglobals on load.
	 *
	 * @param mixed $value Value.
	 *
	 * @return mixed
	 */
	function wp_unslash( $value ) {
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	/**
	 * Core's tag stripper.
	 *
	 * @param string $value Value.
	 *
	 * @return string
	 */
	function wp_strip_all_tags( string $value ): string {
		$value = (string) preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $value );

		return trim( (string) strip_tags( $value ) );
	}
}

if ( ! function_exists( 'wp_is_mobile' ) ) {
	/**
	 * Core's own answer, hint first.
	 *
	 * Copied from wp-includes/vars.php rather than invented, because the
	 * point of deferring to core is getting core's answer.
	 *
	 * @return bool
	 */
	function wp_is_mobile(): bool {
		if ( isset( $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ) ) {
			return '?1' === $_SERVER['HTTP_SEC_CH_UA_MOBILE'];
		}

		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		foreach ( array( 'Mobile', 'Android', 'Silk/', 'Kindle', 'BlackBerry', 'Opera Mini', 'Opera Mobi' ) as $needle ) {
			if ( str_contains( (string) $_SERVER['HTTP_USER_AGENT'], $needle ) ) {
				return true;
			}
		}

		return false;
	}
}

/**
 * Forget anything a test put in $_SERVER.
 *
 * @return void
 */
function ua_reset_server(): void {
	unset( $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_SEC_CH_UA_MOBILE'], $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] );
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

/*
 * And src/Functions.php again: it is a Composer `files` entry, so it already
 * ran when PHPUnit loaded the autoloader -- before ABSPATH was defined, so it
 * returned without declaring anything. `require`, not `require_once`.
 */
require dirname( __DIR__ ) . '/src/Functions.php';
