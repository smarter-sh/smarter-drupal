<?php

declare(strict_types=1);

namespace Drupal\smarter_chat\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smarter_chat\EmbedConfig;
use Drupal\smarter_chat\Validation;

/**
 * The settings form, at /admin/config/services/smarter-chat.
 *
 * See doc/adr/004-configuration.md.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'smarter_chat_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['smarter_chat.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $settings = EmbedConfig::settings($this->config('smarter_chat.settings')->get() ?? []);
    $origin = EmbedConfig::origin($this->getRequest()->getSchemeAndHttpHost());

    $form['chatbot_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Chatbot'),
      '#open' => TRUE,
      '#description' => $this->t("Your chatbot is a deployed LLMClient on a Smarter platform. Its manifest must allow this site's origin, in spec.config.prompt.allowedOrigins: <code>@origin</code>", ['@origin' => $origin]),
    ];
    $form['chatbot_settings']['chatbot'] = [
      '#type' => 'url',
      '#title' => $this->t('Chatbot url'),
      '#default_value' => $settings['chatbot'],
      '#description' => $this->t('The url of the deployed LLMClient, or its custom domain.'),
      '#attributes' => ['placeholder' => 'https://support.3141-5926-5359.api.example.com/'],
    ];
    $form['chatbot_settings']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Api key'),
      '#default_value' => $settings['api_key'],
      '#maxlength' => 256,
      '#description' => $this->t('Only for an LLMClient that requires one. It is written into every page, where any visitor can read it, and it is exported with the configuration: set it in settings.php instead, with <code>@override</code>', ['@override' => "\$config['smarter_chat.settings']['api_key'] = getenv('SMARTER_API_KEY');"]),
    ];

    $form['launcher_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Launcher'),
      '#open' => TRUE,
      '#description' => $this->t("A chat button in a corner of every page that isn't an admin page. To put the chat in a region instead, place the Smarter Chat block."),
    ];
    $form['launcher_settings']['launcher'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show on every page'),
      '#default_value' => $settings['launcher'],
    ];
    $form['launcher_settings']['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Position'),
      '#options' => [
        'bottom-right' => $this->t('Bottom right'),
        'bottom-left' => $this->t('Bottom left'),
      ],
      '#default_value' => $settings['position'],
    ];
    $form['launcher_settings']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $settings['label'],
      '#maxlength' => 60,
      '#description' => $this->t("The button's name, for screen readers."),
    ];
    $form['launcher_settings']['color'] = [
      '#type' => 'color',
      '#title' => $this->t('Button color'),
      '#default_value' => $settings['color'],
    ];
    $form['launcher_settings']['open'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Start open'),
      '#default_value' => $settings['open'],
    ];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
    ];
    $form['advanced']['script_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Script url'),
      '#default_value' => $settings['script_url'],
      '#description' => $this->t("Empty: the chat's script, @smarter.sh/ui-chat @version, is downloaded from jsDelivr. To host it yourself, copy its dist/embed.js and dist/embed/ together, e.g. to /libraries/smarter-chat/, and enter the url of embed.js.", ['@version' => EmbedConfig::EMBED_VERSION]),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    foreach (['chatbot', 'script_url'] as $key) {
      $value = trim((string) $form_state->getValue($key));
      if ($value !== '' && Validation::url($value) === '') {
        $form_state->setErrorByName($key, $this->t('Enter an http or https url, without a user name or password.'));
      }
    }
    $api_key = trim((string) $form_state->getValue('api_key'));
    if ($api_key !== '' && Validation::apiKey($api_key) === '') {
      $form_state->setErrorByName('api_key', $this->t('An api key has only letters, digits, and the characters _ . : -'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('smarter_chat.settings');
    $script_url = $config->get('script_url');
    $settings = EmbedConfig::settings([
      'chatbot' => $form_state->getValue('chatbot'),
      'api_key' => $form_state->getValue('api_key'),
      'launcher' => $form_state->getValue('launcher'),
      'position' => $form_state->getValue('position'),
      'label' => $form_state->getValue('label'),
      'color' => $form_state->getValue('color'),
      'open' => $form_state->getValue('open'),
      'script_url' => $form_state->getValue('script_url'),
    ]);
    foreach ($settings as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
    // The script url is part of the library definition, which Drupal caches.
    if ($script_url !== $settings['script_url']) {
      Cache::invalidateTags(['library_info']);
    }
    parent::submitForm($form, $form_state);
  }

}
