<?php

declare(strict_types=1);

namespace Drupal\smarter_chat;

/**
 * Validators shared by the settings form and the block.
 *
 * Every configured value that reaches a page goes through one of these. They
 * return a valid value, or the fallback. No Drupal dependencies, so that they
 * are unit tested without Drupal. See doc/adr/006-security.md.
 */
final class Validation {

  /**
   * The launcher's corners.
   */
  public const POSITIONS = ['bottom-right', 'bottom-left'];

  /**
   * An http(s) url with a host, without credentials, spaces or quotes.
   *
   * @param mixed $value
   *   The value to validate.
   *
   * @return string
   *   The url, trimmed, or '' if it isn't valid.
   */
  public static function url(mixed $value): string {
    if (!is_string($value)) {
      return '';
    }
    $value = trim($value);
    if ($value === '' || preg_match('/[\s"\'<>\\\\`]/', $value)) {
      return '';
    }
    $parts = parse_url($value);
    if (!is_array($parts) || empty($parts['host']) || empty($parts['scheme'])) {
      return '';
    }
    if (!in_array(strtolower($parts['scheme']), ['http', 'https'], TRUE)) {
      return '';
    }
    if (isset($parts['user']) || isset($parts['pass'])) {
      return '';
    }
    return $value;
  }

  /**
   * A css length: a number with a unit, or a percentage, e.g. 600px or 70vh.
   *
   * @param mixed $value
   *   The value to validate.
   * @param string $fallback
   *   The value to use if it isn't valid.
   *
   * @return string
   *   The length, or the fallback.
   */
  public static function length(mixed $value, string $fallback): string {
    if (is_string($value) && preg_match('/^\d{1,5}(\.\d{1,3})?(px|em|rem|vh|vw|svh|dvh|%)$/', trim($value))) {
      return trim($value);
    }
    return $fallback;
  }

  /**
   * A hex color, e.g. #ff5733.
   *
   * @param mixed $value
   *   The value to validate.
   * @param string $fallback
   *   The value to use if it isn't valid.
   *
   * @return string
   *   The color, or the fallback.
   */
  public static function color(mixed $value, string $fallback): string {
    return is_string($value) && preg_match('/^#([0-9a-fA-F]{3}){1,2}$/', trim($value)) ? trim($value) : $fallback;
  }

  /**
   * One of a set of values.
   *
   * @param mixed $value
   *   The value to validate.
   * @param string[] $choices
   *   The valid values.
   * @param string $fallback
   *   The value to use if it isn't one of them.
   *
   * @return string
   *   The value, or the fallback.
   */
  public static function choice(mixed $value, array $choices, string $fallback): string {
    return is_string($value) && in_array($value, $choices, TRUE) ? $value : $fallback;
  }

  /**
   * One line of plain text, of at most $max characters.
   *
   * @param mixed $value
   *   The value to validate.
   * @param string $fallback
   *   The value to use if it is empty.
   * @param int $max
   *   The maximum length.
   *
   * @return string
   *   The text, or the fallback.
   */
  public static function text(mixed $value, string $fallback, int $max = 100): string {
    $text = is_scalar($value) ? (string) $value : '';
    $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($text)));
    $text = mb_substr($text, 0, $max);
    return $text === '' ? $fallback : $text;
  }

  /**
   * A Smarter api key: letters, digits and a few separators, or ''.
   *
   * @param mixed $value
   *   The value to validate.
   *
   * @return string
   *   The key, or ''.
   */
  public static function apiKey(mixed $value): string {
    $value = is_string($value) ? trim($value) : '';
    return preg_match('/^[A-Za-z0-9_.:-]{1,256}$/', $value) ? $value : '';
  }

}
