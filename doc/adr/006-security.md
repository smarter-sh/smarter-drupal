# ADR-006: Security

## Status

Accepted

Same principles as Smarter Chat for WordPress's [ADR-006](https://github.com/smarter-sh/smarter-wordpress/blob/main/doc/adr/006-security.md).

## Context

The module writes configuration into every page, and a block's settings are edited by anyone who may administer blocks.

## Decision

- **Permissions.** `administer smarter chat` (restrict access) guards the settings form. Placing the block needs Drupal's `administer blocks`.
- **Validate on input**, in the form's and the block's `validate*()` methods, with `Validation`: `chatbot` and `script_url` are `http(s)` urls without credentials or quotes; `color` is a hex color; sizes are css lengths; `position` is one of its values.
- **Escape on output** with Drupal's render system: the block's element is an `html_tag` render array, whose attributes Drupal escapes; `drupalSettings` is JSON that Drupal encodes safely.
- **The api key is public** in the page. The form says so, and recommends an LLMClient without api keys and with a budget, or a key dedicated to the site.
- **Origins.** The form shows the site's origin, which the LLMClient's `allowedOrigins` must list.

## Consequences

- **Positive:**
  - No markup is built by string concatenation.
- **Negative:**
  - Site owners must understand that an api key in a page is public.

## Related ADRs

- [ADR-004: Configuration](004-configuration.md)
