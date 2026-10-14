# ADR-013: Licensing

## Status

Accepted

## Context

Drupal.org requires every project's code to be licensed GPL-2.0-or-later. Smarter and `@smarter.sh/ui-chat` are AGPL-3.0-or-later.

## Decision

The module is **GPL-2.0-or-later**. It doesn't contain the embed script (ADR-003), which visitors' browsers load from jsDelivr, or from the site, under its own license.

## Consequences

- **Positive:**
  - Acceptable on Drupal.org, with no third-party code in the repository.
- **Negative:**
  - The module's license differs from Smarter's.

## Related ADRs

- [ADR-003: The Embed Script Is an External Library](003-external-library.md)
