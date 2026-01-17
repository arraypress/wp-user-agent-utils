<?php
/**
 * UserAgent Utility Class
 *
 * Provides utility functions for user agent detection, browser identification,
 * device type detection, and bot recognition.
 *
 * @package ArrayPress\UserAgentUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\UserAgentUtils;

/**
 * UserAgent Class
 *
 * Core operations for working with user agent strings.
 */
class UserAgent {

	/**
	 * List of known web browsers and their identifying strings.
	 * Ordered by specificity - most specific patterns FIRST to avoid false matches.
	 * Updated for 2024/2025 and tested for accuracy.
	 *
	 * @var array
	 */
	protected static array $browsers = [
		// Electron-based applications
		'Electron'          => 'Electron\/([0-9.]+)',

		// Mobile WebViews (must come before mobile browsers)
		'Android WebView'   => 'Android.*wv.*Chrome\/([0-9.]+)',
		'iOS WebView'       => 'Mobile.*Safari.*AppleWebKit(?!.*Version)',

		// Specific mobile browsers
		'Chrome iOS'        => 'CriOS\/([0-9.]+)',
		'Firefox iOS'       => 'FxiOS\/([0-9.]+)',
		'DuckDuckGo iOS'    => 'DuckDuckGo\/([0-9.]+)',
		'Safari Mobile'     => '(?:iPhone|iPad|iPod).+Version\/([0-9.]+).+Safari',
		'Samsung Browser'   => 'SamsungBrowser\/([0-9.]+)',
		'UC Browser'        => 'UCBrowser\/([0-9.]+)',

		// Desktop browsers with specific identifiers (before generic Chrome)
		'Edge'              => 'Edg(?:e|A|iOS)?\/([0-9.]+)',
		'Opera'             => 'OPR\/([0-9.]+)|Opera\/([0-9.]+)',
		'Brave'             => 'Brave\/([0-9.]+)',
		'Vivaldi'           => 'Vivaldi\/([0-9.]+)',
		'Chrome OS'         => 'CrOS.+Chrome\/([0-9.]+)',

		// Generic patterns (must come after specific ones)
		'Chrome Mobile'     => 'Chrome\/([0-9.]+).*Mobile(?!.*(?:Edge|OPR|Opera|Brave|Vivaldi))',
		'Chrome'            => 'Chrome\/([0-9.]+)(?!.*(?:Edge|OPR|Opera|Brave|Vivaldi|Mobile|wv))',
		'Firefox'           => 'Firefox\/([0-9.]+)',
		'Safari'            => 'Version\/([0-9.]+).+Safari(?!.*Chrome)',

		// Legacy browsers
		'Internet Explorer' => 'MSIE ([0-9.]+)|Trident.*rv:([0-9.]+)',
	];

	/**
	 * List of known operating systems with version detection patterns.
	 * Updated for 2024/2025 OS versions.
	 *
	 * @var array
	 */
	protected static array $operating_systems = [
		'iOS'        => 'iPhone OS ([0-9._]+)|iPad.*OS ([0-9._]+)|iPod.*OS ([0-9._]+)|CPU.*OS ([0-9._]+)',
		'Android'    => 'Android ([0-9.]+)',
		'Windows 11' => 'Windows NT 10\.0.*(?:Build 22000|Build 22H2)',
		'Windows 10' => 'Windows NT 10\.0',
		'Windows'    => 'Windows NT ([0-9.]+)',
		'macOS'      => 'Mac OS X ([0-9._]+)|Intel Mac OS X ([0-9._]+)',
		'Linux'      => 'Linux(?!.*Android)',
		'Chrome OS'  => 'CrOS',
		'Ubuntu'     => 'Ubuntu',
	];

	/**
	 * List of known bot/crawler user agent patterns.
	 * Updated for 2024/2025 with AI bots and modern crawlers.
	 *
	 * @var array
	 */
	protected static array $bots = [
		// Search engine bots
		'Googlebot',
		'Google-InspectionTool',
		'Google-Extended',
		'GoogleOther',
		'bingbot',
		'BingPreview',
		'Baiduspider',
		'DuckDuckBot',
		'YandexBot',

		// AI/LLM bots (2024/2025)
		'GPTBot',
		'ChatGPT-User',
		'OAI-SearchBot',
		'ClaudeBot',
		'Claude-Web',
		'PerplexityBot',
		'Meta-ExternalAgent',
		'CCBot',
		'ImagesiftBot',
		'Bytespider',
		'Anthropic-AI',

		// Social media bots
		'facebookexternalhit',
		'Twitterbot',
		'LinkedInBot',
		'WhatsApp',
		'TelegramBot',
		'Slackbot',
		'Discordbot',

		// Other major bots
		'Applebot',
		'SemrushBot',
		'AhrefsBot',
		'MJ12bot',
		'DotBot',

		// Generic patterns
		'spider',
		'crawler',
		'bot',
		'scraper',

		// Programmatic/CLI tools
		'curl',
		'wget',
		'python-requests',
		'python-urllib',
		'python',
		'Go-http-client',
		'Java',
		'Ruby',
		'Perl',
		'PHP',
		'node-fetch',
		'axios',
		'libwww-perl',
		'HTTPClient',
		'PostmanRuntime',
		'insomnia',
	];

	/**
	 * Get the current user agent string.
	 *
	 * @return string The sanitized user agent string or empty string if not available.
	 */
	public static function get(): string {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return '';
		}

		$user_agent = wp_unslash( $_SERVER['HTTP_USER_AGENT'] );

		return wp_strip_all_tags( $user_agent );
	}

	/**
	 * Get the detected browser name.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return string|null The browser name or null if not detected.
	 */
	public static function get_browser( ?string $user_agent = null ): ?string {
		$ua = $user_agent ?? self::get();
		if ( empty( $ua ) ) {
			return null;
		}

		foreach ( self::$browsers as $browser => $pattern ) {
			if ( preg_match( "/$pattern/i", $ua ) ) {
				return $browser;
			}
		}

		return null;
	}

	/**
	 * Get the operating system.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return string|null The OS name or null if not detected.
	 */
	public static function get_os( ?string $user_agent = null ): ?string {
		$ua = $user_agent ?? self::get();
		if ( empty( $ua ) ) {
			return null;
		}

		foreach ( self::$operating_systems as $os => $pattern ) {
			if ( preg_match( "/$pattern/i", $ua ) ) {
				return $os;
			}
		}

		return null;
	}

	/**
	 * Check if the user agent is a tablet device.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return bool True if tablet device.
	 */
	public static function is_tablet( ?string $user_agent = null ): bool {
		$ua = $user_agent ?? self::get();
		if ( empty( $ua ) ) {
			return false;
		}

		// iPad is always a tablet
		if ( stripos( $ua, 'iPad' ) !== false ) {
			return true;
		}

		// Android tablet (has Android but not Mobile)
		if ( stripos( $ua, 'Android' ) !== false && stripos( $ua, 'Mobile' ) === false ) {
			return true;
		}

		// Other tablet indicators
		$tablet_patterns = [ 'Tablet', 'PlayBook', 'Kindle', 'Silk', 'Surface' ];
		foreach ( $tablet_patterns as $pattern ) {
			if ( stripos( $ua, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the user agent is a mobile device (phone, not tablet).
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return bool True if mobile phone device.
	 */
	public static function is_mobile( ?string $user_agent = null ): bool {
		$ua = $user_agent ?? self::get();
		if ( empty( $ua ) ) {
			return false;
		}

		// Check for tablet first - tablets are not "mobile" in this context
		if ( self::is_tablet( $ua ) ) {
			return false;
		}

		$mobile_patterns = [
			'Mobile',
			'iPhone',
			'iPod',
			'Android',
			'BlackBerry',
			'Windows Phone',
			'webOS',
			'Opera Mini',
			'Opera Mobi',
			'IEMobile',
		];

		foreach ( $mobile_patterns as $pattern ) {
			if ( stripos( $ua, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the user agent is a desktop device.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return bool True if desktop device.
	 */
	public static function is_desktop( ?string $user_agent = null ): bool {
		$ua = $user_agent ?? self::get();
		if ( empty( $ua ) ) {
			return false;
		}

		return ! self::is_mobile( $ua ) && ! self::is_tablet( $ua ) && ! self::is_bot( $ua );
	}

	/**
	 * Check if the user agent is a bot/crawler.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return bool True if bot/crawler.
	 */
	public static function is_bot( ?string $user_agent = null ): bool {
		$ua = $user_agent ?? self::get();
		if ( empty( $ua ) ) {
			return false;
		}

		$bot_pattern = implode( '|', self::$bots );

		return (bool) preg_match( "/$bot_pattern/i", $ua );
	}

	/**
	 * Get device type as a string.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return string Device type: 'mobile', 'tablet', 'desktop', 'bot', or 'unknown'.
	 */
	public static function get_device_type( ?string $user_agent = null ): string {
		$ua = $user_agent ?? self::get();

		if ( empty( $ua ) ) {
			return 'unknown';
		}

		if ( self::is_bot( $ua ) ) {
			return 'bot';
		}

		if ( self::is_tablet( $ua ) ) {
			return 'tablet';
		}

		if ( self::is_mobile( $ua ) ) {
			return 'mobile';
		}

		if ( self::is_desktop( $ua ) ) {
			return 'desktop';
		}

		return 'unknown';
	}

	/**
	 * Get a formatted user agent string for display/storage.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return string Formatted string in format "Browser on OS".
	 */
	public static function get_formatted( ?string $user_agent = null ): string {
		$ua = $user_agent ?? self::get();

		$browser = self::get_browser( $ua ) ?? 'Unknown Browser';
		$os      = self::get_os( $ua ) ?? 'Unknown OS';

		return sprintf( '%s on %s', $browser, $os );
	}

	/**
	 * Check if current browser matches specified browser.
	 *
	 * @param string      $browser    Browser name to check (case-insensitive).
	 * @param string|null $user_agent Optional user agent string.
	 *
	 * @return bool True if browser matches.
	 */
	public static function is_browser( string $browser, ?string $user_agent = null ): bool {
		$detected_browser = self::get_browser( $user_agent );

		if ( ! $detected_browser ) {
			return false;
		}

		return strcasecmp( $detected_browser, $browser ) === 0;
	}

	/**
	 * Check if current OS matches specified OS.
	 *
	 * @param string      $os         OS name to check (case-insensitive).
	 * @param string|null $user_agent Optional user agent string.
	 *
	 * @return bool True if OS matches.
	 */
	public static function is_os( string $os, ?string $user_agent = null ): bool {
		$detected_os = self::get_os( $user_agent );

		if ( ! $detected_os ) {
			return false;
		}

		return strcasecmp( $detected_os, $os ) === 0;
	}

	/**
	 * Check if current device type matches specified type.
	 *
	 * @param string      $type       Device type to check (case-insensitive).
	 * @param string|null $user_agent Optional user agent string.
	 *
	 * @return bool True if device type matches.
	 */
	public static function is_device_type( string $type, ?string $user_agent = null ): bool {
		$detected_type = self::get_device_type( $user_agent );

		return strcasecmp( $detected_type, $type ) === 0;
	}

	/** -------------------------------------------------------------------------
	 * Options Methods (for building select dropdowns, etc.)
	 * ------------------------------------------------------------------------ */

	/**
	 * Get list of detectable browsers.
	 *
	 * Returns browser names that can be detected by this library.
	 * Useful for building select options or validation.
	 *
	 * @param bool $as_options If true, returns array of ['value' => '', 'label' => ''] format.
	 *
	 * @return array
	 */
	public static function get_browsers( bool $as_options = false ): array {
		$browsers = [
			'Chrome'            => __( 'Chrome', 'arraypress' ),
			'Chrome Mobile'     => __( 'Chrome Mobile', 'arraypress' ),
			'Chrome iOS'        => __( 'Chrome iOS', 'arraypress' ),
			'Chrome OS'         => __( 'Chrome OS', 'arraypress' ),
			'Firefox'           => __( 'Firefox', 'arraypress' ),
			'Firefox iOS'       => __( 'Firefox iOS', 'arraypress' ),
			'Safari'            => __( 'Safari', 'arraypress' ),
			'Safari Mobile'     => __( 'Safari Mobile', 'arraypress' ),
			'Edge'              => __( 'Edge', 'arraypress' ),
			'Opera'             => __( 'Opera', 'arraypress' ),
			'Brave'             => __( 'Brave', 'arraypress' ),
			'Vivaldi'           => __( 'Vivaldi', 'arraypress' ),
			'Samsung Browser'   => __( 'Samsung Browser', 'arraypress' ),
			'UC Browser'        => __( 'UC Browser', 'arraypress' ),
			'DuckDuckGo iOS'    => __( 'DuckDuckGo iOS', 'arraypress' ),
			'Internet Explorer' => __( 'Internet Explorer', 'arraypress' ),
			'Android WebView'   => __( 'Android WebView', 'arraypress' ),
			'iOS WebView'       => __( 'iOS WebView', 'arraypress' ),
			'Electron'          => __( 'Electron', 'arraypress' ),
		];

		if ( ! $as_options ) {
			return $browsers;
		}

		return self::to_options( $browsers );
	}

	/**
	 * Get list of detectable operating systems.
	 *
	 * Returns OS names that can be detected by this library.
	 * Useful for building select options or validation.
	 *
	 * @param bool $as_options If true, returns array of ['value' => '', 'label' => ''] format.
	 *
	 * @return array
	 */
	public static function get_operating_systems( bool $as_options = false ): array {
		$operating_systems = [
			'Windows'    => __( 'Windows (any version)', 'arraypress' ),
			'Windows 10' => __( 'Windows 10', 'arraypress' ),
			'Windows 11' => __( 'Windows 11', 'arraypress' ),
			'macOS'      => __( 'macOS', 'arraypress' ),
			'iOS'        => __( 'iOS', 'arraypress' ),
			'Android'    => __( 'Android', 'arraypress' ),
			'Linux'      => __( 'Linux', 'arraypress' ),
			'Ubuntu'     => __( 'Ubuntu', 'arraypress' ),
			'Chrome OS'  => __( 'Chrome OS', 'arraypress' ),
		];

		if ( ! $as_options ) {
			return $operating_systems;
		}

		return self::to_options( $operating_systems );
	}

	/**
	 * Get list of device types.
	 *
	 * Returns device types that can be detected by this library.
	 * Useful for building select options or validation.
	 *
	 * @param bool $as_options If true, returns array of ['value' => '', 'label' => ''] format.
	 *
	 * @return array
	 */
	public static function get_device_types( bool $as_options = false ): array {
		$device_types = [
			'desktop' => __( 'Desktop', 'arraypress' ),
			'mobile'  => __( 'Mobile', 'arraypress' ),
			'tablet'  => __( 'Tablet', 'arraypress' ),
			'bot'     => __( 'Bot/Crawler', 'arraypress' ),
			'unknown' => __( 'Unknown', 'arraypress' ),
		];

		if ( ! $as_options ) {
			return $device_types;
		}

		return self::to_options( $device_types );
	}

	/**
	 * Convert key/value array to options format.
	 *
	 * Converts ['key' => 'label'] to [['value' => 'key', 'label' => 'label'], ...]
	 *
	 * @param array $items Key/value array.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	protected static function to_options( array $items ): array {
		$options = [];

		foreach ( $items as $value => $label ) {
			$options[] = [
				'value' => $value,
				'label' => $label,
			];
		}

		return $options;
	}

}