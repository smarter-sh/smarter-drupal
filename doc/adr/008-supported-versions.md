# ADR-008: Supported Drupal and PHP Versions

## Status

Accepted

## Context

Drupal 10.3 and 11 are the supported major versions of Drupal core. Drupal 10 requires PHP 8.1, and Drupal 11 PHP 8.3.

## Decision

- `core_version_requirement: ^10.3 || ^11`.
- PHP 8.1 or later. The module uses constructor property promotion and typed properties, but nothing newer than 8.1.
- The module uses only stable core apis: `ConfigFormBase`, block plugins with attributes and annotations (both, for 10.3), `hook_page_attachments`, `hook_library_info_alter`.

## Consequences

- **Positive:**
  - Covers every supported Drupal site.
- **Negative:**
  - Block plugins declare both the `#[Block]` attribute and the `@Block` annotation until Drupal 10 support ends.

## Related ADRs

- [ADR-009: Testing](009-testing.md)
