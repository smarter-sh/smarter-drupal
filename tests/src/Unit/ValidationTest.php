<?php

declare(strict_types=1);

namespace Drupal\Tests\smarter_chat\Unit;

use Drupal\smarter_chat\Validation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validators.
 *
 * They accept what the embed script accepts, and nothing that could escape an
 * attribute, a url or a css value.
 */
#[CoversClass(Validation::class)]
final class ValidationTest extends TestCase {

  /**
   * Http and https urls are valid.
   */
  public function testUrlAcceptsHttpAndHttps(): void {
    $this->assertSame('https://support.3141-5926-5359.api.example.com/', Validation::url(' https://support.3141-5926-5359.api.example.com/ '));
    $this->assertSame('http://localhost:9357/api/v1/llm-clients/3/', Validation::url('http://localhost:9357/api/v1/llm-clients/3/'));
  }

  /**
   * Anything else is not.
   */
  #[DataProvider('invalidUrls')]
  public function testUrlRejectsAnythingElse(mixed $value): void {
    $this->assertSame('', Validation::url($value));
  }

  /**
   * Urls that must be rejected.
   */
  public static function invalidUrls(): array {
    return [
      'empty' => [''],
      'not a string' => [['https://example.com/']],
      'javascript' => ['javascript:alert(1)'],
      'data' => ['data:text/html,<script>alert(1)</script>'],
      'no host' => ['https:///path'],
      'relative' => ['/chat/'],
      'quote' => ['https://example.com/"><script>'],
      'backtick' => ['https://example.com/`x`'],
      'space' => ['https://example.com/ onload=alert(1)'],
      'credentials' => ['https://user:secret@example.com/'],
      'ftp' => ['ftp://example.com/'],
      'unparseable' => ['http://:80'],
    ];
  }

  /**
   * Css lengths.
   */
  public function testLength(): void {
    $this->assertSame('600px', Validation::length('600px', 'x'));
    $this->assertSame('70vh', Validation::length(' 70vh ', 'x'));
    $this->assertSame('100%', Validation::length('100%', 'x'));
    $this->assertSame('x', Validation::length('600', 'x'));
    $this->assertSame('x', Validation::length('600px;background:red', 'x'));
    $this->assertSame('x', Validation::length(NULL, 'x'));
  }

  /**
   * Hex colors.
   */
  public function testColor(): void {
    $this->assertSame('#ff5733', Validation::color('#ff5733', '#000'));
    $this->assertSame('#ABC', Validation::color(' #ABC ', '#000'));
    $this->assertSame('#000', Validation::color('red; background:url(x)', '#000'));
    $this->assertSame('#000', Validation::color(42, '#000'));
  }

  /**
   * Choices, compared strictly.
   */
  public function testChoice(): void {
    $this->assertSame('bottom-left', Validation::choice('bottom-left', Validation::POSITIONS, 'bottom-right'));
    $this->assertSame('bottom-right', Validation::choice('top', Validation::POSITIONS, 'bottom-right'));
    $this->assertSame('bottom-right', Validation::choice(1, ['1'], 'bottom-right'));
  }

  /**
   * Plain text.
   */
  public function testText(): void {
    $this->assertSame('Ask us', Validation::text(" Ask \n  us ", 'Chat'));
    $this->assertSame('Ask', Validation::text('<b>Ask</b>', 'Chat'));
    $this->assertSame('Chat', Validation::text('<img src=x onerror=alert(1)>', 'Chat'));
    $this->assertSame('Chat', Validation::text([], 'Chat'));
    $this->assertSame('Ché', Validation::text('Chérie', 'Chat', 3));
  }

  /**
   * Api keys.
   */
  public function testApiKey(): void {
    $this->assertSame('a1b2_c3-d4.e5:f6', Validation::apiKey(' a1b2_c3-d4.e5:f6 '));
    $this->assertSame('', Validation::apiKey('key"><script>'));
    $this->assertSame('', Validation::apiKey(NULL));
  }

}
