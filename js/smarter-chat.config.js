/**
 * @file
 * Copies drupalSettings.smarterChat, the launcher's options, to window.SmarterChatConfig, which the embed script of
 * @smarter.sh/ui-chat reads when it runs. This script is synchronous and comes first, so it runs before the embed
 * script, which is async. See doc/adr/002-embed-script.md.
 */
((drupalSettings) => {
  if (drupalSettings && drupalSettings.smarterChat) {
    window.SmarterChatConfig = drupalSettings.smarterChat;
  }
})(window.drupalSettings);
