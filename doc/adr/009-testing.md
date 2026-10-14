# ADR-009: Testing

## Status

Accepted

Follows the Python SDK's [ADR-009: Testing](https://github.com/smarter-sh/smarter-python/blob/main/doc/adr/009-testing.md), and Smarter Chat for WordPress's.

## Context

Drupal's kernel and functional tests need a Drupal codebase and a database, which make them slow to set up for a module of this size. Most of the module's logic, validation and the mapping from settings to the embed script's options, doesn't need Drupal at all.

## Decision

- **Unit tests** with PHPUnit, in `tests/src/Unit/`, for the classes without Drupal dependencies (`Validation`, `EmbedConfig`), run by `make test`, at least 95% coverage of those classes.
- **A smoke test**, `make smoke`, that runs Drupal 11 in Docker with SQLite, installs it with Drush, enables the module from the working tree, configures it with `drush config:set`, places the block, and checks the rendered pages with `curl`: the launcher's settings and script, the block's element, escaping, the admin route without the launcher, and cache invalidation. It covers the form, the block plugin and the hooks, which unit tests can't.
- PHP isn't required on the developer's machine: `make test` runs in Docker when `php` isn't installed.

## Alternatives Considered

- **Kernel and functional tests (`KernelTestBase`, `BrowserTestBase`)**, run in a Drupal checkout. The right tool when the module grows; the smoke test covers the same ground today in one script.

## Consequences

- **Positive:**
  - Fast unit tests, and a real Drupal for everything else.
- **Negative:**
  - The form and the block have no unit tests; the smoke test must cover them.

## Related ADRs

- [ADR-010: CI/CD and Releases](010-ci-cd.md)
