# ADR-001: Scope of the Module

## Status

Accepted

Same decision as Smarter Chat for WordPress's [ADR-001](https://github.com/smarter-sh/smarter-wordpress/blob/main/doc/adr/001-scope.md).

## Context

A Smarter chatbot is an LLMClient, deployed to its own host on a Smarter platform. Drupal powers many government, university and enterprise sites, whose owners want that chatbot on their pages without writing JavaScript. `@smarter.sh/ui-chat`'s embed script renders the chat from a script tag (ADR-002), so what remains is Drupal's: configuration, permissions, and where the chat appears.

## Decision

The module, `smarter_chat`, provides:

- a **settings form** (Configuration → Web services → Smarter Chat) for the chatbot's url and the launcher's options (ADR-004),
- a **site-wide launcher**, a chat button in a corner of every non-admin page, when it is on (ADR-005),
- a **Smarter Chat block**, which puts the chat in any region, with Drupal's block visibility (ADR-005).

It has no chat code, makes no requests to Smarter from PHP, stores no conversations, and doesn't manage LLMClients.

## Alternatives Considered

- **A Smarter client in PHP** that manages LLMClients, or proxies prompts. See the WordPress plugin's ADR-001: the same reasons apply.
- **A Twig snippet in the theme.** Not configurable by site builders, and lost when the theme changes.

## Consequences

- **Positive:**
  - Small, with the same behavior as every other Smarter integration.
- **Negative:**
  - An api key, if the LLMClient needs one, is public in the page (ADR-006).

## Related ADRs

- [ADR-002: The Embed Script Is the Chat](002-embed-script.md)
- [ADR-005: Placement: Launcher and Block](005-placement.md)
