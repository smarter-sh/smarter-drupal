# ADR-007: Caching

## Status

Accepted

## Context

Drupal caches pages and render arrays, for anonymous visitors (Page Cache) and for everyone (Dynamic Page Cache). An attachment that doesn't declare what it depends on stays stale after its settings change.

## Decision

- The launcher's attachments carry the `config:smarter_chat.settings` cache tag, and the `route` cache context (the launcher depends on whether the route is an admin route).
- The block's render array carries the same tag, plus the block's own cacheability, which Drupal adds.
- Saving the settings invalidates `library_info` too, because the script url is part of the library definition (ADR-003).

## Consequences

- **Positive:**
  - A change in the settings is visible on the next request, with every cache on.
- **Negative:**
  - Saving the settings invalidates every cached page that has the launcher, which is every page.

## Related ADRs

- [ADR-003: The Embed Script Is an External Library](003-external-library.md)
- [ADR-005: Placement: Launcher and Block](005-placement.md)
