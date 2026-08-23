<?php
/**
 * User agent detection.
 *
 * Real strings, taken as browsers actually send them. The detection is a
 * series of substring checks and the order matters -- every Chrome string
 * contains "Safari", every Edge string contains "Chrome" -- so a check in the
 * wrong order reports the wrong browser for the majority of visitors.
 *
 * @package ArrayPress\UserAgentUtils
 */

declare( strict_types=1 );

namespace ArrayPress\UserAgentUtils\Tests;

use ArrayPress\UserAgentUtils\UserAgent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class UserAgentTest
 */
final class UserAgentTest extends TestCase {

	/**
	 * Real user agent strings.
	 */
	private const CHROME  = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
	private const SAFARI  = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15';
	private const FIREFOX = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0';
	private const EDGE    = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0';
	private const IPHONE  = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Mobile/15E148 Safari/604.1';
	private const IPAD    = 'Mozilla/5.0 (iPad; CPU OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Mobile/15E148 Safari/604.1';
	private const ANDROID = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';
	private const GBOT    = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
	private const CURL    = 'curl/8.4.0';

	/**
	 * Clean up the header between tests.
	 */
	protected function tearDown(): void {
		unset( $_SERVER['HTTP_USER_AGENT'] );

		parent::tearDown();
	}

	/**
	 * The browser is named correctly.
	 *
	 * @param string $ua       The user agent.
	 * @param string $expected The browser.
	 */
	#[DataProvider( 'browsers' )]
	public function test_the_browser_is_named( string $ua, string $expected ): void {
		$this->assertSame( $expected, UserAgent::get_browser( $ua ) );
	}

	/**
	 * Browsers and the strings they send.
	 *
	 * @return array
	 */
	public static function browsers(): array {
		return [
			'chrome'  => [ self::CHROME, 'Chrome' ],
			'safari'  => [ self::SAFARI, 'Safari' ],
			'firefox' => [ self::FIREFOX, 'Firefox' ],
			'edge'    => [ self::EDGE, 'Edge' ],
		];
	}

	/**
	 * Chrome is not reported as Safari.
	 *
	 * Every Chrome string ends in "Safari/537.36". Checking Safari first
	 * misreports every Chrome visitor, which is most of them.
	 */
	public function test_chrome_is_not_mistaken_for_safari(): void {
		$this->assertSame( 'Chrome', UserAgent::get_browser( self::CHROME ) );
	}

	/**
	 * Edge is not reported as Chrome.
	 *
	 * Edge sends a full Chrome string and appends "Edg/". Checking Chrome
	 * first misreports every Edge visitor.
	 */
	public function test_edge_is_not_mistaken_for_chrome(): void {
		$this->assertSame( 'Edge', UserAgent::get_browser( self::EDGE ) );
	}

	/**
	 * The operating system is named.
	 */
	public function test_the_operating_system_is_named(): void {
		$this->assertNotNull( UserAgent::get_os( self::CHROME ) );
		$this->assertNotNull( UserAgent::get_os( self::FIREFOX ) );
		$this->assertNotNull( UserAgent::get_os( self::ANDROID ) );
	}

	/**
	 * Device type is classified.
	 */
	public function test_device_types(): void {
		$this->assertTrue( UserAgent::is_mobile( self::IPHONE ) );
		$this->assertFalse( UserAgent::is_mobile( self::CHROME ) );

		$this->assertTrue( UserAgent::is_tablet( self::IPAD ) );
		$this->assertFalse( UserAgent::is_tablet( self::IPHONE ) );

		$this->assertTrue( UserAgent::is_desktop( self::CHROME ) );
		$this->assertFalse( UserAgent::is_desktop( self::IPHONE ) );
	}

	/**
	 * An iPad is a tablet, not a mobile.
	 *
	 * Both send "Mobile/15E148", so a naive check calls the iPad a phone.
	 */
	public function test_an_ipad_is_a_tablet_not_a_phone(): void {
		$this->assertTrue( UserAgent::is_tablet( self::IPAD ) );
		$this->assertFalse( UserAgent::is_mobile( self::IPAD ), 'An iPad was classified as a phone.' );
	}

	/**
	 * Bots are recognised.
	 */
	public function test_bots_are_recognised(): void {
		$this->assertTrue( UserAgent::is_bot( self::GBOT ) );
		$this->assertTrue( UserAgent::is_bot( self::CURL ) );
		$this->assertFalse( UserAgent::is_bot( self::CHROME ) );
	}

	/**
	 * A bot is not also a desktop.
	 */
	public function test_a_bot_is_not_a_desktop(): void {
		$this->assertFalse( UserAgent::is_desktop( self::GBOT ) );
	}

	/**
	 * An empty agent is nothing at all, and does not crash.
	 */
	public function test_an_empty_agent_is_survivable(): void {
		$this->assertFalse( UserAgent::is_bot( '' ) );
		$this->assertFalse( UserAgent::is_mobile( '' ) );
		$this->assertNull( UserAgent::get_browser( '' ) );
		$this->assertNull( UserAgent::get_os( '' ) );
	}

	/**
	 * The formatted form always returns a string.
	 */
	public function test_the_formatted_form_is_always_a_string(): void {
		$this->assertSame( 'Chrome on macOS', UserAgent::get_formatted( self::CHROME ) );
		$this->assertStringContainsString( 'Unknown', UserAgent::get_formatted( 'nonsense' ) );
	}

	/**
	 * The header is read, unslashed and stripped of markup.
	 *
	 * WordPress runs add_magic_quotes() over $_SERVER, so a quoted agent
	 * arrives with backslashes and would not compare equal to itself.
	 */
	public function test_the_header_is_read_safely(): void {
		$_SERVER['HTTP_USER_AGENT'] = self::CHROME;
		$this->assertSame( self::CHROME, UserAgent::get() );

		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 \\"quoted\\"';
		$this->assertStringNotContainsString( '\\\\', UserAgent::get(), 'The value was not unslashed.' );

		$_SERVER['HTTP_USER_AGENT'] = 'Bad<script>alert(1)</script>';
		$this->assertStringNotContainsString( '<script>', UserAgent::get() );
	}

	/**
	 * A missing header is an empty string, not a warning.
	 */
	public function test_a_missing_header_is_empty(): void {
		unset( $_SERVER['HTTP_USER_AGENT'] );

		$this->assertSame( '', UserAgent::get() );
	}
}
