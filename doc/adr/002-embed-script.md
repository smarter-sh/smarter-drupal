# ADR-002: The Embed Script Is the Chat

## Status

Accepted

Same decision as Smarter Chat for WordPress's [ADR-002](https://github.com/smarter-sh/smarter-wordpress/blob/main/doc/adr/002-embed-script.md).

## Context

The chat is a React 19 component. Drupal's front end is jQuery-free Drupal behaviors, and themes bring their own css. `@smarter.sh/ui-chat` publishes `dist/embed.js`, one classic script that includes React and the chat, renders it in a Shadow DOM, and reads its options from `<smarter-chat>` attributes or `window.SmarterChatConfig`.

## Decision

The module renders nothing itself. The launcher passes its options as `drupalSettings.smarterChat`, which a ten-line script (`js/smarter-chat.config.js`) copies to `window.SmarterChatConfig` before the embed script runs; the block renders a `<smarter-chat>` element. The module's settings are the embed script's options, in snake_case, mapped in one place (`EmbedConfig`).

The module is built and tested with one version of the embed script, `EmbedConfig::EMBED_VERSION`.

## Alternatives Considered

- **A Drupal behavior that renders the chat.** That is the embed script's job, and would mean a React build in the module.
- **The embed script's data- attributes on its script tag.** Drupal's library system doesn't set per-page attributes on a library's script; `drupalSettings` is Drupal's way to pass values to JavaScript.

## Consequences

- **Positive:**
  - No JavaScript build. No dependency on Drupal's front-end libraries but `core/drupalSettings`.
- **Negative:**
  - About 330 kB of JavaScript (gzipped), once, on pages with the chat.

## Related ADRs

- [ADR-003: The Embed Script Is an External Library](003-external-library.md)
