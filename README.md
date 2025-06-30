# WordPress UserAgent Utilities - Device & Browser Detection

A lightweight WordPress library for user agent detection, browser identification, and device type recognition. Perfect for responsive design, analytics, and conditional functionality.

## Features

* 🎯 **Clean API**: WordPress-style snake_case methods with consistent interfaces
* 🌐 **Browser Detection**: Identify Chrome, Firefox, Safari, Edge, and 15+ browsers with versions
* 📱 **Device Recognition**: Mobile, tablet, desktop detection with fallback to `wp_is_mobile()`
* 🤖 **Bot Detection**: Identify search engines, crawlers, and AI bots (2024/2025 updated)
* 💻 **OS Detection**: Windows, macOS, iOS, Android, Linux with version support
* ⚡ **WebView Detection**: Identify in-app browsers and Electron applications
* 🔍 **Comprehensive Info**: Get all device data in a single method call

## Requirements

* PHP 7.4 or later
* WordPress 5.0 or later

## Installation

```bash
composer require arraypress/wp-useragent-utils
```

## Basic Usage

### User Agent String

```php
use ArrayPress\UserAgentUtils\UserAgent;

// Get current user's user agent
$user_agent = UserAgent::get();
// Returns: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36..."

// Test with custom user agent
$custom_ua = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)...";
$browser   = UserAgent::get_browser( $custom_ua );
```

### Browser Detection

```php
// Get browser name
$browser = UserAgent::get_browser();
// Returns: "Chrome", "Firefox", "Safari", "Edge", etc.

// Get browser version
$version = UserAgent::get_browser_version();
// Returns: "120.0.0.0"

// Check specific browser (clean method)
if ( UserAgent::is_browser( 'Chrome' ) ) {
	// Chrome-specific functionality
}

// Check browser version requirements
if ( UserAgent::is_browser_version( 'Safari', '>=', '14.0' ) ) {
	// Use modern Safari features
}

// Compatibility checks
if ( UserAgent::is_browser_version( 'Chrome', '<', '91.0' ) ) {
	// Load polyfills for older Chrome
}
```

### Operating System Detection

```php
// Get operating system
$os = UserAgent::get_os();
// Returns: "Windows", "macOS", "iOS", "Android", "Linux", etc.

// Get OS version
$os_version = UserAgent::get_os_version();
// Returns: "10.0", "17.1", "14", etc.

// Check specific OS (clean method)
if ( UserAgent::is_os( 'iOS' ) ) {
	// iOS-specific handling
}

// Check OS version requirements
if ( UserAgent::is_os_version( 'iOS', '>=', '15.0' ) ) {
	// Use iOS 15+ features
}

// Mobile OS detection
if ( UserAgent::is_os( 'Android' ) || UserAgent::is_os( 'iOS' ) ) {
	// Mobile-specific code
}
```

### Device Type Detection

```php
// Basic device detection (uses wp_is_mobile() when possible)
if ( UserAgent::is_mobile() ) {
	// Mobile device
}

if ( UserAgent::is_tablet() ) {
	// Tablet device
}

if ( UserAgent::is_desktop() ) {
	// Desktop device
}

// Get device type as string
$device = UserAgent::get_device_type();
// Returns: "mobile", "tablet", "desktop", or "unknown"
```

### Bot & Special Detection

```php
// Check for bots/crawlers (includes 2024/2025 AI bots)
if ( UserAgent::is_bot() ) {
	// Search engine, AI bot, or crawler
	// Skip analytics, show cached content, etc.
}

// Check for Electron apps
if ( UserAgent::is_electron() ) {
	// Desktop app using Electron (Discord, Obsidian, etc.)
}

// Check for WebViews
if ( UserAgent::is_webview() ) {
	// In-app browser or embedded webview
}
```

### Formatted Output (EDD Compatible)

```php
// Get EDD Browser library compatible format
$formatted = UserAgent::get_formatted();
// Returns: "Chrome 120.0.0.0/Windows"

// Custom user agent
$custom_formatted = UserAgent::get_formatted( $custom_ua );
// Returns: "Safari Mobile 17.1/iOS"

// Different examples:
// "Edge 120.0.0.0/Windows"
// "Firefox 120.0/macOS" 
// "Samsung Browser 23.0/Android"
// "Chrome iOS 120.0.6099.119/iOS"
```

### Comprehensive Device Info

```php
// Get all device information at once
$info = UserAgent::get_device_info();

/*
Returns array:
[
    'user_agent' => 'Mozilla/5.0...',
    'browser' => 'Chrome',
    'browser_version' => '120.0.0.0',
    'os' => 'Windows',
    'os_version' => '10.0',
    'device_type' => 'desktop',
    'is_mobile' => false,
    'is_tablet' => false,
    'is_desktop' => true,
    'is_bot' => false,
    'is_electron' => false,
    'is_webview' => false,
    'formatted' => 'Chrome 120.0.0.0/Windows'
]
*/
```

## Common Use Cases

### EDD Browser Library Replacement

```php
// Replace EDD's get_user_agent() function
function get_user_agent(): string {
	return UserAgent::get_formatted();
}

// Or use directly
$user_agent_string = UserAgent::get_formatted();
// Returns: "Chrome 120.0.0.0/Windows" (same format as EDD)
```

### Conditional Asset Loading

```php
function enqueue_device_specific_assets() {
	$browser = UserAgent::get_browser();
	$device  = UserAgent::get_device_type();

	// Load device-specific styles
	wp_enqueue_style( "styles-{$device}", "css/{$device}.css" );

	// Browser-specific fixes
	if ( $browser === 'Internet Explorer' ) {
		wp_enqueue_script( 'ie-polyfills', 'js/ie-fixes.js' );
	}

	// Mobile-specific JavaScript
	if ( UserAgent::is_mobile() ) {
		wp_enqueue_script( 'touch-gestures', 'js/touch.js' );
	}
}
add_action( 'wp_enqueue_scripts', 'enqueue_device_specific_assets' );
```

### Analytics & Tracking

```php
function track_visitor_info() {
	// Skip bot traffic (includes AI bots)
	if ( UserAgent::is_bot() ) {
		return;
	}

	$info = UserAgent::get_device_info();

	// Log visitor information
	$visitor_data = [
		'browser'     => $info['browser'],
		'os'          => $info['os'],
		'device_type' => $info['device_type'],
		'formatted'   => $info['formatted'],
		'timestamp'   => current_time( 'mysql' )
	];

	// Store in database or send to analytics
	update_option( 'visitor_stats', $visitor_data );
}
add_action( 'wp_head', 'track_visitor_info' );
```

### Responsive Content

```php
function display_device_optimized_content() {
	$device_type = UserAgent::get_device_type();

	switch ( $device_type ) {
		case 'mobile':
			// Show mobile-optimized content
			echo '<div class="mobile-hero">Swipe to explore</div>';
			break;

		case 'tablet':
			// Tablet-specific layout
			echo '<div class="tablet-grid">Touch-friendly interface</div>';
			break;

		case 'desktop':
			// Full desktop experience
			echo '<div class="desktop-hero">Full-featured experience</div>';
			break;
	}
}

```

### Browser Feature Detection

```php
function check_browser_compatibility() {
	$browser = UserAgent::get_browser();
	$version = UserAgent::get_browser_version();

	// Check for modern browser features
	$modern_browsers = [ 'Chrome', 'Firefox', 'Safari', 'Edge' ];

	if ( ! in_array( $browser, $modern_browsers ) ) {
		// Show upgrade notice
		add_action( 'wp_footer', function () {
			echo '<div class="browser-notice">For the best experience, please update your browser.</div>';
		} );
	}

	// Internet Explorer specific handling
	if ( $browser === 'Internet Explorer' ) {
		// Redirect to compatibility page or show notice
		wp_redirect( '/browser-upgrade/' );
		exit;
	}
}
add_action( 'template_redirect', 'check_browser_compatibility' );
```

### Security & Bot Filtering (Updated 2024/2025)

```php
function handle_bot_requests() {
	if ( ! UserAgent::is_bot() ) {
		return;
	}

	$user_agent = UserAgent::get();

	// Allow known good bots (including AI bots for SEO)
	$good_bots = [ 'Googlebot', 'bingbot', 'Applebot' ];
	foreach ( $good_bots as $bot ) {
		if ( stripos( $user_agent, $bot ) !== false ) {
			// Serve cached content for SEO bots
			serve_cached_content();

			return;
		}
	}

	// Handle AI bots separately
	$ai_bots = [ 'GPTBot', 'ClaudeBot', 'ChatGPT-User' ];
	foreach ( $ai_bots as $bot ) {
		if ( stripos( $user_agent, $bot ) !== false ) {
			// Custom handling for AI training bots
			handle_ai_bot_request();

			return;
		}
	}

	// Block or limit unknown bots
	http_response_code( 429 ); // Too Many Requests
	exit( 'Rate limited' );
}
add_action( 'init', 'handle_bot_requests' );
```

### WebView & App Detection

```php
function handle_webview_requests() {
	if ( UserAgent::is_webview() ) {
		// Adjust for in-app browsers
		add_filter( 'body_class', function ( $classes ) {
			$classes[] = 'webview';

			return $classes;
		} );

		// Disable certain features that don't work well in webviews
		remove_action( 'wp_head', 'wp_print_head_scripts', 9 );
	}

	if ( UserAgent::is_electron() ) {
		// Handle Electron desktop apps
		add_filter( 'body_class', function ( $classes ) {
			$classes[] = 'electron-app';

			return $classes;
		} );
	}
}
add_action( 'wp_head', 'handle_webview_requests' );
```

### Admin Interface Optimization

```php
function optimize_admin_for_device() {
	if ( ! is_admin() ) {
		return;
	}

	if ( UserAgent::is_mobile() ) {
		// Mobile admin optimizations
		add_action( 'admin_enqueue_scripts', function () {
			wp_enqueue_style( 'mobile-admin', 'css/mobile-admin.css' );
		} );

		// Simplify admin menus on mobile
		add_filter( 'wp_nav_menu_args', function ( $args ) {
			$args['depth'] = 1; // Limit menu depth

			return $args;
		} );
	}

	if ( UserAgent::is_tablet() ) {
		// Tablet-specific admin adjustments
		add_action( 'admin_head', function () {
			echo '<style>.wp-admin { font-size: 16px; }</style>';
		} );
	}
}
add_action( 'admin_init', 'optimize_admin_for_device' );
```

## Supported Browsers (Updated 2024/2025)

### Desktop Browsers
- **Chrome** - 67% global market share, including Chromium-based browsers
- **Safari** - 18% global market share, macOS Safari
- **Edge** - 5% global market share, Chromium-based Edge
- **Firefox** - 3% global market share, all versions
- **Opera** - Including both old Presto and new Chromium-based versions
- **Brave** - Privacy-focused Chromium browser
- **Vivaldi** - Feature-rich Chromium browser
- **Internet Explorer** - Legacy support

### Mobile Browsers
- **Chrome Mobile** - 67% mobile market share, Android Chrome
- **Safari Mobile** - 23% mobile market share, iOS Safari
- **Samsung Browser** - 3% global but dominant on Samsung devices
- **Chrome iOS** - Chrome on iOS devices
- **Firefox iOS** - Firefox on iOS devices
- **UC Browser** - Popular in Asia and emerging markets
- **DuckDuckGo Browser** - Privacy-focused mobile browser
- **Android WebView** - In-app browsers and embedded views

### Special Cases
- **Electron Apps** - Desktop applications (Discord, Slack, etc.)
- **WebViews** - In-app browsers and embedded views
- **Chrome OS** - ChromeOS browsers

## Supported Operating Systems

- **Windows** - All versions with NT detection, including Windows 11
- **macOS** - All versions with detailed version parsing
- **iOS** - iPhone, iPad, iPod with iOS 18+ support
- **Android** - All versions including Android 15
- **Linux** - Including Ubuntu and other distributions
- **Chrome OS** - ChromeOS detection

## Bot Detection (Updated 2024/2025)

### Search Engine Bots
- **Google** (Googlebot, Google-InspectionTool, Google-Extended, GoogleOther)
- **Microsoft Bing** (bingbot, BingPreview)
- **Baidu** (Baiduspider)
- **DuckDuckGo** (DuckDuckBot)
- **Yandex** (YandexBot)

### AI/LLM Bots (New 2024/2025)
- **OpenAI** (GPTBot, ChatGPT-User, OAI-SearchBot)
- **Anthropic** (ClaudeBot, Claude-Web, Anthropic-AI)
- **Meta** (Meta-ExternalAgent)
- **Perplexity** (PerplexityBot)
- **Common Crawl** (CCBot)
- **ByteDance** (Bytespider - powers TikTok's AI)
- **Image processing** (ImagesiftBot)

### Social Media Crawlers
- **Facebook** (facebookexternalhit, Meta-ExternalAgent)
- **Twitter/X** (Twitterbot)
- **LinkedIn** (LinkedInBot)
- **WhatsApp** link previews
- **Telegram** (TelegramBot)
- **Slack** (Slackbot)
- **Discord** (Discordbot)

### SEO & Analytics Bots
- **Apple** (Applebot)
- **Semrush** (SemrushBot)
- **Ahrefs** (AhrefsBot)
- **Majestic** (MJ12bot)
- **Moz** (DotBot)

## Method Reference

### Core Methods
- `get()` - Get current user agent string
- `get_browser( ?string $ua )` - Get browser name
- `get_browser_version( ?string $ua )` - Get browser version
- `get_os( ?string $ua )` - Get operating system
- `get_os_version( ?string $ua )` - Get OS version
- `get_device_type( ?string $ua )` - Get device type as string
- `get_formatted( ?string $ua )` - Get EDD Browser library compatible format

### Device Detection
- `is_mobile( ?string $ua )` - Check if mobile device
- `is_tablet( ?string $ua )` - Check if tablet
- `is_desktop( ?string $ua )` - Check if desktop
- `is_bot( ?string $ua )` - Check if bot/crawler
- `is_electron( ?string $ua )` - Check if Electron app
- `is_webview( ?string $ua )` - Check if WebView

### Comprehensive
- `get_device_info( ?string $ua )` - Get all device information

## WordPress Integration

- **Fallback Support**: Uses `wp_is_mobile()` when no specific user agent provided
- **Sanitization**: All user agent strings are sanitized using WordPress functions
- **Performance**: Lightweight detection with optimized regex patterns
- **Compatibility**: Works with all WordPress caching and optimization plugins

## Pattern Accuracy

All browser and bot detection patterns have been:
- ✅ **Tested with real 2024/2025 user agent strings**
- ✅ **Ordered by specificity to avoid false matches**
- ✅ **Updated for current market share and browser usage**
- ✅ **Verified against edge cases and WebView detection**

## Requirements

- PHP 7.4+
- WordPress 5.0+

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL-2.0-or-later License.

## Support

- [Documentation](https://github.com/arraypress/wp-useragent-utils)
- [Issue Tracker](https://github.com/arraypress/wp-useragent-utils/issues)