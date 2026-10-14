# ADR-014: Documentation

## Status

Accepted

## Context

The module's users are Drupal site builders, who read the module's help page and its project page, and Smarter users, who read docs.smarter.sh.

## Decision

- **`hook_help()`** describes the module on its help page, with links to its settings and to docs.smarter.sh.
- **docs.smarter.sh** has the module's page, `smarter-framework/smarter-drupal.rst` in the platform's repository, listed in Smarter APIs and SDKs.
- **`README.md`** follows Drupal.org's README template (Introduction, Requirements, Installation, Configuration, Maintainers), and adds a Development section for contributors.
- Every class, method and function has a docblock, as Drupal's standards require. Prose is evergreen.

## Consequences

- **Positive:**
  - Each audience has one place to look.
- **Negative:**
  - The settings are described in two places, kept in step by hand.
