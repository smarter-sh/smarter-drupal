# ADR-011: Semantic Versioning

## Status

Accepted

## Context

Drupal.org supports semantic versioning for contributed projects (`1.2.3`, without the old `8.x-` prefix). Drupal reads a module's version from its packaging, not from the repository.

## Decision

- Versions follow semantic versioning, set by semantic-release from conventional commit messages, as tags (`v1.2.3`). `smarter_chat.info.yml` and `composer.json` have no `version` key: Drupal.org's packaging adds it to the info file, and Composer reads it from the tag.
- Updating `EmbedConfig::EMBED_VERSION` to a new minor version of the embed script is a `feat`; to a patch, a `fix`.
- A configuration key is never renamed or removed in a minor release; when one must go, a `hook_update_N()` migrates it.

## Consequences

- **Positive:**
  - No version strings to keep in step in the repository.
- **Negative:**
  - A git checkout of the module shows no version in Drupal's Extend page.

## Related ADRs

- [ADR-010: CI/CD and Releases](010-ci-cd.md)
