---
name: smarter-drupal-development
description: Start here for any change to the smarter-drupal repository, Smarter Chat for Drupal (the smarter_chat module), which adds a Smarter chatbot to a Drupal 10.3/11 site with a settings form, a site-wide launcher and a block, by loading @smarter.sh/ui-chat's embed script as an external library. Gives the rules, the module's architecture, how a setting flows from configuration to the page, caching, how to add an option, and an index of the narrower skills.
---

# Smarter Chat for Drupal: Development

The module is a thin layer over the embed script of `@smarter.sh/ui-chat`
(`../smarter-chat/src/embed/`), which is the chat. The module decides where the
chat appears and with which options (`doc/adr/001-scope.md`). Its WordPress
sibling (`../smarter-wordpress`) is the same design in WordPress; read it when
you change behavior that both should share.

## Non-negotiable rules

1. **Never execute git write commands.** See `smarter-git-commits`.
2. **`make test` after every change; `make smoke` after changing anything that
   Drupal runs** (form, block, hooks, routing, libraries, schema).
3. **Render arrays and `drupalSettings` only**: never build markup by string
   concatenation, never `#markup` with a configured value.
4. **Declare cacheability**: `config:smarter_chat.settings` on everything the
   module renders or attaches (`doc/adr/007-caching.md`).
5. **Pure logic goes in `Validation` and `EmbedConfig`**, which have no Drupal
   dependencies and are unit tested. Drupal classes stay thin.
6. **A feature that the embed script lacks goes into `../smarter-chat` first.**

## Architecture

| File                                    | Responsibility                                                                          |
| --------------------------------------- | --------------------------------------------------------------------------------------- |
| `src/Validation.php`                    | Validators: url, css length, hex color, choice, text, api key                           |
| `src/EmbedConfig.php`                   | `EMBED_VERSION`, the CDN url, `launcher(settings)`, `element(settings, block)`          |
| `smarter_chat.module`                   | `hook_help`, `hook_page_attachments` (launcher), `hook_library_info_alter` (script url) |
| `src/Form/SettingsForm.php`             | `ConfigFormBase` at `/admin/config/services/smarter-chat`                               |
| `src/Plugin/Block/SmarterChatBlock.php` | The block: chatbot, height, width; `html_tag` render array                              |
| `smarter_chat.libraries.yml`            | `config` (the settings copier), `embed` (external, async), `launcher` (both)            |
| `js/smarter-chat.config.js`             | `drupalSettings.smarterChat` → `window.SmarterChatConfig`                               |

### How a setting reaches the page

1. The form saves `smarter_chat.settings`, after `Validation` checks each field,
   and invalidates `library_info` when `script_url` changes.
2. `smarter_chat_page_attachments()`: on a non-admin route, with a chatbot and
   the launcher on, it attaches `smarter_chat/launcher` and
   `drupalSettings.smarterChat = EmbedConfig::launcher($settings)`, after
   `hook_smarter_chat_launcher_alter()`, with the config's cache tag and the
   `route` context.
3. `js/smarter-chat.config.js` (synchronous, earlier in the page) sets
   `window.SmarterChatConfig`; then the async embed script reads it.
4. The block renders `EmbedConfig::element()` as
   `['#type' => 'html_tag', '#tag' => 'smarter-chat', '#attributes' => ...]`
   and attaches `smarter_chat/embed`.

### Adding an option

1. Check `../smarter-chat/src/embed/options.ts` for it, and its version.
2. Add it to `config/install/smarter_chat.settings.yml` and
   `config/schema/smarter_chat.schema.yml`, with a `hook_update_N()` that sets
   its default on existing sites.
3. Validate it in `Validation`, map it in `EmbedConfig`, add it to the form
   (and the block, if it applies inline).
4. Unit tests, the smoke test, `README.md`, and
   `../smarter/docs/source/smarter-framework/smarter-drupal.rst`.

## Related skills

| Skill                    | Use it when                                               |
| ------------------------ | --------------------------------------------------------- |
| `smarter-drupal-testing` | Writing or running the unit or smoke tests                |
| `smarter-code-quality`   | Before finishing: PHPCS (Drupal) and the pre-commit hooks |
| `smarter-git-commits`    | Asked to write a commit message                           |
| `monthly-maintenance`    | Bumping the embed script, Drupal core and dependencies    |
