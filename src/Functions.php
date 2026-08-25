<?php
/**
 * Global User Agent Helper Functions
 *
 * Provides convenient global functions for common user agent operations.
 * These functions are wrappers around the ArrayPress\UserAgentUtils\UserAgent class.
 *
 * Functions included:
 * - get_user_agent() - Get the current user agent string
 * - get_browser() - Get the detected browser name
 * - get_device_type() - Get device type (mobile/tablet/desktop)
 * - is_mobile() - Check if mobile device
 * - is_bot() - Check if bot/crawler
 *
 * @package ArrayPress\UserAgentUtils
 * @since   1.0.0
 */

// Exit if accessed directly
// return, not exit. This file is a Composer `files` autoload entry, so it runs
// whenever anything requires the autoloader -- phpunit, phpcs, a composer
// script. Ending the process there kills the tool with status 0 and no output,
// which reads as success: a lint that never looked at a file, or a test suite
// that never ran, both report as passing.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use ArrayPress\UserAgentUtils\Request;

if ( ! function_exists( 'get_user_agent' ) ) {
	/**
	 * Get the current user agent string.
	 *
	 * @since 1.0.0
	 * @return string The sanitized user agent string or empty string.
	 */
	function get_user_agent(): string {
		return Request::agent();
	}
}
