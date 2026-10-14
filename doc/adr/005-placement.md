# ADR-005: Placement: Launcher and Block

## Status

Accepted

## Context

Sites want the chat as a launcher on every page, or in the content of some pages. Drupal places content with blocks, whose visibility (pages, content types, roles) site builders already know.

## Decision

- **The launcher** is attached in `hook_page_attachments()` when it is on and a chatbot is set, on every page that isn't an admin route. Modules can change its options, or remove it from a page, with `hook_smarter_chat_launcher_alter(array &$config)` (an empty array removes it).
- **The Smarter Chat block** (`smarter_chat_block`) renders a `<smarter-chat>` element with the block's chatbot (or the settings'), height and width, and attaches `smarter_chat/embed`. Its visibility is Drupal's block visibility.
- The site's api key is sent only to the settings' chatbot, never to a chatbot that a block names.

## Alternatives Considered

- **A text format filter, `[smarter_chat]`.** Blocks and Layout Builder cover placement, without a filter's caching and security questions.
- **Launcher visibility rules of our own (paths, roles).** The alter hook covers the rare case; most sites want it everywhere.

## Consequences

- **Positive:**
  - Works with Layout Builder, Gin, Olivero and any theme that has regions.
- **Negative:**
  - Hiding the launcher on some pages needs a few lines of code.

## Related ADRs

- [ADR-002: The Embed Script Is the Chat](002-embed-script.md)
- [ADR-007: Caching](007-caching.md)
