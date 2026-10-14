<?php

/**
 * @file
 * Hooks provided by the Smarter Chat module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alters the launcher's options, before they are attached to the page.
 *
 * The options are the embed script's, in camelCase: chatbot, mode, position,
 * label, color, apiKey, open, width, height, ... See
 * https://github.com/smarter-sh/smarter-chat#embedding-in-any-website.
 *
 * @param array $launcher
 *   The options. Set it to [] to remove the launcher from the current page.
 */
function hook_smarter_chat_launcher_alter(array &$launcher): void {
  // No launcher on the front page.
  if (\Drupal::service('path.matcher')->isFrontPage()) {
    $launcher = [];
  }
}

/**
 * @} End of "addtogroup hooks".
 */
