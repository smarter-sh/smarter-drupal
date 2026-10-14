<?php

declare(strict_types=1);

namespace Drupal\smarter_chat;

/**
 * Maps the module's settings to the embed script's options.
 *
 * The settings are the embed script's options in snake_case. This is the only
 * place that knows the embed script's names (camelCase in
 * window.SmarterChatConfig, kebab-case as attributes). No Drupal dependencies,
 * so that it is unit tested without Drupal. See doc/adr/002-embed-script.md.
 */
final class EmbedConfig {

  /**
   * The version of @smarter.sh/ui-chat that the module is tested with.
   *
   * It is also in smarter_chat.libraries.yml, which a unit test keeps in step.
   */
  public const EMBED_VERSION = '0.9.0';

  /**
   * The defaults of the settings, as config/install has them.
   */
  public const DEFAULTS = [
    'chatbot' => '',
    'api_key' => '',
    'launcher' => TRUE,
    'position' => 'bottom-right',
    'label' => 'Chat',
    'color' => '#ff5733',
    'open' => FALSE,
    'script_url' => '',
  ];

  /**
   * The default size of a chat in a block.
   */
  public const BLOCK_DEFAULTS = [
    'chatbot' => '',
    'height' => '600px',
    'width' => '100%',
  ];

  /**
   * The embed script's url on jsDelivr.
   *
   * @return string
   *   The url of the version that the module is tested with.
   */
  public static function cdnUrl(): string {
    return 'https://cdn.jsdelivr.net/npm/@smarter.sh/ui-chat@' . self::EMBED_VERSION . '/dist/embed.js';
  }

  /**
   * The settings, complete and valid.
   *
   * @param array $settings
   *   The saved configuration, which may be incomplete or edited by hand.
   *
   * @return array
   *   The settings, with each missing or invalid value replaced by its default.
   */
  public static function settings(array $settings): array {
    $settings += self::DEFAULTS;
    return [
      'chatbot' => Validation::url($settings['chatbot']),
      'api_key' => Validation::apiKey($settings['api_key']),
      'launcher' => (bool) $settings['launcher'],
      'position' => Validation::choice($settings['position'], Validation::POSITIONS, self::DEFAULTS['position']),
      'label' => Validation::text($settings['label'], self::DEFAULTS['label'], 60),
      'color' => Validation::color($settings['color'], self::DEFAULTS['color']),
      'open' => (bool) $settings['open'],
      'script_url' => Validation::url($settings['script_url']),
    ];
  }

  /**
   * The embed script's url: the self-hosted one, or jsDelivr's.
   *
   * @param array $settings
   *   The settings.
   *
   * @return string
   *   The url.
   */
  public static function scriptUrl(array $settings): string {
    $url = self::settings($settings)['script_url'];
    return $url !== '' ? $url : self::cdnUrl();
  }

  /**
   * The launcher's window.SmarterChatConfig, or [] if there is no launcher.
   *
   * @param array $settings
   *   The settings.
   *
   * @return array
   *   The embed script's options, in camelCase.
   */
  public static function launcher(array $settings): array {
    $settings = self::settings($settings);
    if ($settings['chatbot'] === '' || !$settings['launcher']) {
      return [];
    }
    $config = [
      'chatbot' => $settings['chatbot'],
      'mode' => 'launcher',
      'position' => $settings['position'],
      'label' => $settings['label'],
      'color' => $settings['color'],
    ];
    if ($settings['api_key'] !== '') {
      $config['apiKey'] = $settings['api_key'];
    }
    if ($settings['open']) {
      $config['open'] = TRUE;
    }
    return $config;
  }

  /**
   * The attributes of a block's <smarter-chat> element.
   *
   * The site's api key is sent only to the settings' chatbot, never to a
   * chatbot that a block names.
   *
   * @param array $settings
   *   The settings.
   * @param array $block
   *   The block's configuration: chatbot, height and width.
   *
   * @return array
   *   The element's attributes, in kebab-case, or [] without a chatbot.
   */
  public static function element(array $settings, array $block): array {
    $settings = self::settings($settings);
    $block += self::BLOCK_DEFAULTS;
    $chatbot = Validation::url($block['chatbot']);
    if ($chatbot === '') {
      $chatbot = $settings['chatbot'];
    }
    if ($chatbot === '') {
      return [];
    }
    $attributes = [
      'chatbot' => $chatbot,
      'height' => Validation::length($block['height'], self::BLOCK_DEFAULTS['height']),
      'width' => Validation::length($block['width'], self::BLOCK_DEFAULTS['width']),
    ];
    if ($settings['api_key'] !== '' && rtrim($chatbot, '/') === rtrim($settings['chatbot'], '/')) {
      $attributes['api-key'] = $settings['api_key'];
    }
    return $attributes;
  }

  /**
   * The origin of a url, e.g. https://www.example.com, for allowedOrigins.
   *
   * @param string $url
   *   The site's base url.
   *
   * @return string
   *   The origin, lowercased, or '' if the url has no host.
   */
  public static function origin(string $url): string {
    $parts = parse_url($url);
    if (!is_array($parts) || empty($parts['host'])) {
      return '';
    }
    $port = isset($parts['port']) ? ':' . $parts['port'] : '';
    return strtolower(($parts['scheme'] ?? 'https') . '://' . $parts['host'] . $port);
  }

}
