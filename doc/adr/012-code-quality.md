# ADR-012: Code Quality

## Status

Accepted

## Context

Drupal.org reviews contributed projects against Drupal's coding standards, which [Coder](https://www.drupal.org/project/coder) implements for PHPCS.

## Decision

- **PHP, YAML and Twig:** PHPCS with Coder's `Drupal` and `DrupalPractice` standards, configured in `phpcs.xml.dist`, run by `make lint` and by pre-commit.
- **Everything else:** Prettier, codespell, the generic pre-commit hooks, and commitlint, as in every Smarter repository.

## Consequences

- **Positive:**
  - The code is what Drupal.org's reviewers expect.
- **Negative:**
  - Drupal's style (two-space indentation, `camelCase` methods, docblocks on everything) differs from the WordPress plugin's.

## Related ADRs

- [ADR-008: Supported Drupal and PHP Versions](008-supported-versions.md)
