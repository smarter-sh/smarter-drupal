# ADR-010: CI/CD and Releases

## Status

Accepted

## Context

Every change should be tested, and releases should reach both Composer users of GitHub and, eventually, Drupal.org.

## Decision

GitHub Actions. `test.yml` runs PHPCS (Drupal and DrupalPractice standards) and the unit tests on PHP 8.1 and 8.3, for every push and pull request. `pushMain.yml` runs semantic-release on `main`, `beta` and `alpha`, which tags the release (ADR-011). A site installs a release with Composer from this repository (`composer require smarter-sh/smarter-drupal`, with a `vcs` repository), until the project is on Drupal.org, whose packaging then builds releases from the same tags pushed to its GitLab.

## Consequences

- **Positive:**
  - Every release is a tag that Composer can install.
- **Negative:**
  - Publishing to Drupal.org is a manual step: pushing the tags to git.drupalcode.org.

## Related ADRs

- [ADR-009: Testing](009-testing.md)
- [ADR-011: Semantic Versioning](011-versioning.md)
