<?php

declare(strict_types=1);

namespace Drupal\smarter_chat\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\smarter_chat\EmbedConfig;
use Drupal\smarter_chat\Validation;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The Smarter Chat block: the chat, in a region.
 *
 * The attribute and the annotation both declare the block, for Drupal 10.3
 * and 11 (doc/adr/008-supported-versions.md).
 *
 * @Block(
 *   id = "smarter_chat_block",
 *   admin_label = @Translation("Smarter Chat"),
 *   category = @Translation("Smarter")
 * )
 */
#[Block(
  id: 'smarter_chat_block',
  admin_label: new TranslatableMarkup('Smarter Chat'),
  category: new TranslatableMarkup('Smarter'),
)]
final class SmarterChatBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs the block.
   *
   * @param array $configuration
   *   The block's configuration.
   * @param string $plugin_id
   *   The plugin's id.
   * @param mixed $plugin_definition
   *   The plugin's definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory, for the module's settings.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, private readonly ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('config.factory'));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return EmbedConfig::BLOCK_DEFAULTS + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['chatbot'] = [
      '#type' => 'url',
      '#title' => $this->t('Chatbot url'),
      '#default_value' => $this->configuration['chatbot'],
      '#description' => $this->t("Empty: the chatbot in Smarter Chat's settings."),
    ];
    $form['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#default_value' => $this->configuration['height'],
      '#size' => 10,
      '#description' => $this->t('A css length, e.g. 600px or 70vh.'),
    ];
    $form['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#default_value' => $this->configuration['width'],
      '#size' => 10,
      '#description' => $this->t('A css length, e.g. 100% or 480px.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state): void {
    $chatbot = trim((string) $form_state->getValue('chatbot'));
    if ($chatbot !== '' && Validation::url($chatbot) === '') {
      $form_state->setErrorByName('chatbot', $this->t('Enter an http or https url, without a user name or password.'));
    }
    foreach (['height', 'width'] as $key) {
      if (Validation::length($form_state->getValue($key), '') === '') {
        $form_state->setErrorByName($key, $this->t('Enter a css length, e.g. 600px, 70vh or 100%.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    foreach (['chatbot', 'height', 'width'] as $key) {
      $this->configuration[$key] = trim((string) $form_state->getValue($key));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $attributes = EmbedConfig::element($this->configFactory->get('smarter_chat.settings')->get() ?? [], $this->configuration);
    if ($attributes === []) {
      return [];
    }
    // A child, rather than the build itself: Drupal 10's block view builder
    // moves a build's top-level #attributes to the block's wrapper.
    return [
      'chat' => [
        '#type' => 'html_tag',
        '#tag' => 'smarter-chat',
        '#value' => '',
        '#attributes' => $attributes,
      ],
      '#attached' => ['library' => ['smarter_chat/embed']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    return Cache::mergeTags(parent::getCacheTags(), ['config:smarter_chat.settings']);
  }

}
