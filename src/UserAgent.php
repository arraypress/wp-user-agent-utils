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
	 * Get truncated user agent string for storage.
	 *
	 * @param string|null $user_agent Optional user agent string to truncate.
	 * @param int         $max_length Maximum length (default 500 characters).
	 *
	 * @return string Truncated user agent string.
	 */
	public static function get_truncated( ?string $user_agent = null, int $max_length = 500 ): string {
		$ua = $user_agent ?? self::get();

		if ( empty( $ua ) || $max_length <= 0 ) {
			return '';
		}

		// If it's already within limit, return as-is
		if ( strlen( $ua ) <= $max_length ) {
			return $ua;
		}

		// Truncate and add ellipsis if needed
		return substr( $ua, 0, $max_length - 3 ) . '...';
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
	 * Get the browser version.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return string|null The browser version or null if not detected.
	 */
	public static function get_browser_version( ?string $user_agent = null ): ?string {
		$ua = $user_agent ?? self::get();
		if ( empty( $ua ) ) {
			return null;
		}

		foreach ( self::$browsers as $browser => $pattern ) {
			if ( preg_match( "/$pattern/i", $ua, $matches ) ) {
				// Return the first non-empty capture group
				for ( $i = 1; $i < count( $matches ); $i ++ ) {
					if ( ! empty( $matches[ $i ] ) ) {
						return $matches[ $i ];
					}
				}
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
	 * Get the operating system version.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return string|null The OS version or null if not detected.
	 */
	public static function get_os_version( ?string $user_agent = null ): ?string {
		$ua = $user_agent ?? self::get();
		if ( empty( $ua ) ) {
			return null;
		}

		$os = self::get_os( $ua );
		if ( ! $os || ! isset( self::$operating_systems[ $os ] ) ) {
			return null;
		}

		if ( preg_match( '/' . self::$operating_systems[ $os ] . '/i', $ua, $matches ) ) {
			// Return the first non-empty capture group
			for ( $i = 1; $i < count( $matches ); $i ++ ) {
				if ( ! empty( $matches[ $i ] ) ) {
					return str_replace( '_', '.', $matches[ $i ] );
				}
			}
		}

		return null;
	}

	/**
	 * Check if the user agent is a mobile device.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return bool True if mobile device.
	 */
	public static function is_mobile( ?string $user_agent = null ): bool {
		// Use WordPress core function if no specific user agent provided
		if ( $user_agent === null ) {
			return wp_is_mobile();
		}

		$mobile_patterns = [
			'Mobile',
			'Android',
			'iPhone',
			'iPad',
			'iPod',
			'BlackBerry',
			'Windows Phone'
		];

		foreach ( $mobile_patterns as $pattern ) {
			if ( stripos( $user_agent, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the user agent is a tablet.
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

		return (bool) preg_match( '/iPad|Android(?!.*Mobile)|Tablet/i', $ua );
	}

	/**
	 * Check if the user agent is a desktop device.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return bool True if desktop device.
	 */
	public static function is_desktop( ?string $user_agent = null ): bool {
		return ! self::is_mobile( $user_agent ) && ! self::is_tablet( $user_agent );
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
	 * Check if the user agent is an Electron-based application.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return bool True if Electron-based.
	 */
	public static function is_electron( ?string $user_agent = null ): bool {
		$ua = $user_agent ?? self::get();
		if ( empty( $ua ) ) {
			return false;
		}

		return (bool) preg_match( '/Electron/i', $ua );
	}

	/**
	 * Check if the user agent is a WebView.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return bool True if WebView.
	 */
	public static function is_webview( ?string $user_agent = null ): bool {
		$ua = $user_agent ?? self::get();
		if ( empty( $ua ) ) {
			return false;
		}

		return (bool) preg_match( '/wv|WebView/i', $ua );
	}

	/**
	 * Get device type as a string.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return string Device type: 'mobile', 'tablet', 'desktop', or 'unknown'.
	 */
	public static function get_device_type( ?string $user_agent = null ): string {
		if ( self::is_mobile( $user_agent ) ) {
			return 'mobile';
		}

		if ( self::is_tablet( $user_agent ) ) {
			return 'tablet';
		}

		if ( self::is_desktop( $user_agent ) ) {
			return 'desktop';
		}

		return 'unknown';
	}

	/**
	 * Get a formatted user agent string in EDD Browser library format.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return string Formatted string in format "Browser Version/Platform".
	 */
	public static function get_formatted( ?string $user_agent = null ): string {
		$ua = $user_agent ?? self::get();

		$browser  = self::get_browser( $ua ) ?? 'Unknown';
		$version  = self::get_browser_version( $ua ) ?? 'Unknown';
		$platform = self::get_os( $ua ) ?? 'Unknown';

		return sprintf( '%s %s/%s', $browser, $version, $platform );
	}

	/**
	 * Get comprehensive device information.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return array Array of device information.
	 */
	public static function get_device_info( ?string $user_agent = null ): array {
		$ua = $user_agent ?? self::get();

		return [
			'user_agent'      => $ua,
			'browser'         => self::get_browser( $ua ),
			'browser_version' => self::get_browser_version( $ua ),
			'os'              => self::get_os( $ua ),
			'os_version'      => self::get_os_version( $ua ),
			'device_type'     => self::get_device_type( $ua ),
			'is_mobile'       => self::is_mobile( $ua ),
			'is_tablet'       => self::is_tablet( $ua ),
			'is_desktop'      => self::is_desktop( $ua ),
			'is_bot'          => self::is_bot( $ua ),
			'is_electron'     => self::is_electron( $ua ),
			'is_webview'      => self::is_webview( $ua ),
			'formatted'       => self::get_formatted( $ua ),
		];
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
	 * Check browser version against criteria.
	 *
	 * @param string      $browser    Browser name to check.
	 * @param string      $operator   Comparison operator (>=, >, <, <=, ==, !=).
	 * @param string      $version    Version to compare against.
	 * @param string|null $user_agent Optional user agent string.
	 *
	 * @return bool True if version criteria met.
	 */
	public static function is_browser_version( string $browser, string $operator, string $version, ?string $user_agent = null ): bool {
		if ( ! self::is_browser( $browser, $user_agent ) ) {
			return false;
		}

		$detected_version = self::get_browser_version( $user_agent );

		if ( ! $detected_version ) {
			return false;
		}

		return version_compare( $detected_version, $version, $operator );
	}

	/**
	 * Check if the user agent appears suspicious or malformed.
	 *
	 * @param string|null $user_agent Optional user agent string to check.
	 *
	 * @return bool True if suspicious.
	 */
	public static function is_suspicious( ?string $user_agent = null ): bool {
		$ua = $user_agent ?? self::get();

		// Empty or very short user agent
		if ( strlen( $ua ) < 10 ) {
			return true;
		}

		// Already detected as bot
		if ( self::is_bot( $ua ) ) {
			return true;
		}

		return false;
	}

}