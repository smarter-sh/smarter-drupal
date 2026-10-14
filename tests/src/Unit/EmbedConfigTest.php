<?php

declare(strict_types=1);

namespace Drupal\Tests\smarter_chat\Unit;

use Drupal\smarter_chat\EmbedConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests the mapping of the settings to the embed script's options.
 */
#[CoversClass(EmbedConfig::class)]
final class EmbedConfigTest extends TestCase {

  private const CHATBOT = 'https://support.3141-5926-5359.api.example.com/';

  /**
   * The module's directory.
   */
  private static function moduleDir(): string {
    return dirname(__DIR__, 3);
  }

  /**
   * Missing and invalid values are their defaults.
   */
  public function testSettingsAreCompleteAndValid(): void {
    $this->assertSame(EmbedConfig::DEFAULTS, EmbedConfig::settings([]));
    $settings = EmbedConfig::settings([
      'chatbot' => 'javascript:alert(1)',
      'api_key' => '"><script>',
      'position' => 'top',
      'label' => '',
      'color' => 'red',
      'script_url' => 'ftp://example.com/embed.js',
      'launcher' => 0,
      'open' => 1,
    ]);
    $this->assertSame('', $settings['chatbot']);
    $this->assertSame('', $settings['api_key']);
    $this->assertSame('bottom-right', $settings['position']);
    $this->assertSame('Chat', $settings['label']);
    $this->assertSame('#ff5733', $settings['color']);
    $this->assertSame('', $settings['script_url']);
    $this->assertFalse($settings['launcher']);
    $this->assertTrue($settings['open']);
  }

  /**
   * The config/install defaults are EmbedConfig's.
   */
  public function testInstallDefaultsMatch(): void {
    $yaml = (string) file_get_contents(self::moduleDir() . '/config/install/smarter_chat.settings.yml');
    foreach (EmbedConfig::DEFAULTS as $key => $default) {
      $expected = is_bool($default) ? ($default ? 'true' : 'false') : $default;
      $this->assertMatchesRegularExpression('/^' . $key . ": '?" . preg_quote((string) $expected, '/') . "'?$/m", $yaml, $key);
    }
  }

  /**
   * The library's url and EmbedConfig agree on the embed script's version.
   */
  public function testLibraryUsesTheEmbedVersion(): void {
    $yaml = (string) file_get_contents(self::moduleDir() . '/smarter_chat.libraries.yml');
    $this->assertStringContainsString('    ' . EmbedConfig::cdnUrl() . ':', $yaml);
  }

  /**
   * The script is jsDelivr's, unless the site hosts it.
   */
  public function testScriptUrl(): void {
    $this->assertSame('https://cdn.jsdelivr.net/npm/@smarter.sh/ui-chat@' . EmbedConfig::EMBED_VERSION . '/dist/embed.js', EmbedConfig::scriptUrl([]));
    $this->assertSame('https://www.example.com/libraries/smarter-chat/embed.js', EmbedConfig::scriptUrl(['script_url' => 'https://www.example.com/libraries/smarter-chat/embed.js']));
  }

  /**
   * The launcher's options are the embed script's, in camelCase.
   */
  public function testLauncher(): void {
    $this->assertSame([], EmbedConfig::launcher([]));
    $this->assertSame([], EmbedConfig::launcher(['chatbot' => self::CHATBOT, 'launcher' => FALSE]));
    $this->assertSame(
      [
        'chatbot' => self::CHATBOT,
        'mode' => 'launcher',
        'position' => 'bottom-left',
        'label' => 'Help',
        'color' => '#000000',
      ],
      EmbedConfig::launcher([
        'chatbot' => self::CHATBOT,
        'position' => 'bottom-left',
        'label' => 'Help',
        'color' => '#000000',
      ]),
    );
    $launcher = EmbedConfig::launcher(['chatbot' => self::CHATBOT, 'api_key' => 'k', 'open' => TRUE]);
    $this->assertSame('k', $launcher['apiKey']);
    $this->assertTrue($launcher['open']);
  }

  /**
   * A block's element has its own chatbot and size, or the settings'.
   */
  public function testElement(): void {
    $settings = ['chatbot' => self::CHATBOT, 'api_key' => 'site-key'];
    $this->assertSame(
      ['chatbot' => self::CHATBOT, 'height' => '600px', 'width' => '100%', 'api-key' => 'site-key'],
      EmbedConfig::element($settings, []),
    );
    $this->assertSame(
      ['chatbot' => 'https://sales.example.com/', 'height' => '70vh', 'width' => '480px'],
      EmbedConfig::element($settings, [
        'chatbot' => 'https://sales.example.com/',
        'height' => '70vh',
        'width' => '480px',
      ]),
    );
    // The api key goes to the settings' chatbot only, with or without its
    // trailing slash.
    $without_slash = ['chatbot' => rtrim(self::CHATBOT, '/')];
    $this->assertArrayHasKey('api-key', EmbedConfig::element($settings, $without_slash));
    $this->assertSame([], EmbedConfig::element([], []));
  }

  /**
   * A block's hostile values fall back to the defaults.
   */
  public function testElementNeutralizesHostileValues(): void {
    $this->assertSame(
      ['chatbot' => self::CHATBOT, 'height' => '600px', 'width' => '100%'],
      EmbedConfig::element(['chatbot' => self::CHATBOT], [
        'chatbot' => 'javascript:alert(1)',
        'height' => '1px" onload="x',
        'width' => '<b>',
      ]),
    );
  }

  /**
   * The site's origin, for allowedOrigins.
   */
  public function testOrigin(): void {
    $this->assertSame('https://www.example.com', EmbedConfig::origin('https://www.example.com'));
    $this->assertSame('http://localhost:8080', EmbedConfig::origin('http://LOCALHOST:8080/drupal'));
    $this->assertSame('', EmbedConfig::origin('not a url'));
  }

}
