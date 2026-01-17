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
defined( 'ABSPATH' ) || exit;

use ArrayPress\UserAgentUtils\UserAgent;

if ( ! function_exists( 'get_user_agent' ) ) {
	/**
	 * Get the current user agent string.
	 *
	 * @since 1.0.0
	 * @return string The sanitized user agent string or empty string.
	 */
	function get_user_agent(): string {
		return UserAgent::get();
	}
}