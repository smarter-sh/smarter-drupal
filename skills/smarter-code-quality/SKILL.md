---
name: smarter-code-quality
description: Use before finishing any change to the smarter-drupal module, to lint and format it - PHPCS with Coder's Drupal and DrupalPractice standards, Prettier, codespell, the generic pre-commit hooks and commitlint. Explains which tools are authoritative, how to run them under zsh, and Drupal-specific traps (docblocks, dependency injection, t() and translatable strings).
---

# Smarter Chat for Drupal: Code Quality

`.pre-commit-config.yaml` and `phpcs.xml.dist` are the standard. If a hook
passes, the code meets it. Never bypass a hook, and never add a
`phpcs:ignore` without a comment that says why.

| Tool      | Configuration      | Notes                                                   |
| --------- | ------------------ | ------------------------------------------------------- |
| PHPCS     | `phpcs.xml.dist`   | Coder's `Drupal` and `DrupalPractice`; php, module, yml |
| Prettier  | `.prettierrc.json` | Markdown, JSON, JavaScript (not the Drupal yml files)   |
| codespell | `codespell.txt`    |                                                         |

```console
make lint                                              # PHPCS (Docker if php isn't installed)
make lint-fix                                          # PHPCBF, then PHPCS
git diff --name-only | xargs pre-commit run --files
```

## Drupal traps

- `DrupalPractice.Objects.GlobalDrupal`: no `\Drupal::` calls in classes that
  can have services injected (forms, blocks). Inject them with `create()`.
  The `.module` file may use `\Drupal::`.
- Every user-facing string goes through `$this->t()` / `t()`, with
  placeholders (`@origin`), never concatenation.
- Docblocks on every class, method, property and hook implementation
  (`Implements hook_page_attachments().`).
- Prettier skips Drupal's `*.yml` files, whose style is Coder's.
