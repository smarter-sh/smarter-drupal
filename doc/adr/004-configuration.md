# ADR-004: Configuration

## Status

Accepted

## Context

Drupal sites deploy configuration between environments with configuration management (`drush config:export`/`import`), and site builders expect a module's settings there, with a schema.

## Decision

- The settings are simple configuration, `smarter_chat.settings`, with defaults in `config/install/smarter_chat.settings.yml` and types in `config/schema/smarter_chat.schema.yml`: `chatbot`, `api_key`, `launcher`, `position`, `label`, `color`, `open` and `script_url`.
- The form is a `ConfigFormBase`, at `/admin/config/services/smarter-chat`, behind the `administer smarter chat` permission (ADR-006), and validates every field with the same validators that the block uses (`Validation`).
- **The api key is configuration, so it is exported.** The form says so, and recommends setting it in `settings.php` instead (`$config['smarter_chat.settings']['api_key'] = getenv('SMARTER_API_KEY');`), which Drupal's configuration override system applies without exporting it.

## Alternatives Considered

- **State (`\Drupal::state()`).** Not exported, but not deployable either, and invisible to configuration management.
- **The Key module** for the api key. A dependency for a value that is public in the page anyway (ADR-006).

## Consequences

- **Positive:**
  - Settings deploy with the rest of the site's configuration, and can differ per environment through overrides.
- **Negative:**
  - A site builder who ignores the advice exports the api key into git.

## Related ADRs

- [ADR-005: Placement: Launcher and Block](005-placement.md)
- [ADR-006: Security](006-security.md)
