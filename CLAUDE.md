# CLAUDE.md

Guidance for Claude Code (and other coding agents) working in this repository.

smarter-drupal is **Smarter Chat for Drupal**, the Drupal module `smarter_chat`
that adds a Smarter chatbot to a Drupal site: a settings form, a site-wide chat
launcher and a Smarter Chat block. It has no chat code of its own. It loads the
embed script of [`@smarter.sh/ui-chat`](https://github.com/smarter-sh/smarter-chat)
(`../smarter-chat`) as an external library, and passes the script's options in
`drupalSettings` or as a `<smarter-chat>` element. The chatbot is an LLMClient
on a Smarter platform ([smarter-sh/smarter](https://github.com/smarter-sh/smarter), `../smarter`).
Its WordPress sibling, `../smarter-wordpress`, makes the same decisions where
it can.

Detailed know-how lives in the Agent Skills under `skills/` (symlinked to
`.claude/skills`). **Load `smarter-drupal-development` first for any change.**

## Hard rules

1. **Never run git write commands**, here, in `../smarter` or in
   `../smarter-chat`. "Write a git commit" means print the command as text.
   See `smarter-git-commits`.
2. **Run the tests yourself** after any change (`make test`), and the smoke
   test (`make smoke`) when you change the form, the block, a hook, the
   library or the configuration schema. Report failures honestly.
3. **Render arrays, not strings.** Every value reaches the page through
   Drupal's render system or `drupalSettings`. See `doc/adr/006-security.md`.
4. **Cacheability.** Everything the module attaches declares its cache tags
   and contexts. See `doc/adr/007-caching.md`.
5. **The pre-commit hooks and PHPCS (Drupal, DrupalPractice) are the lint
   standard.** See `smarter-code-quality`.
6. **Coverage:** at least 90% of the unit-tested classes.
7. **This repository is public.** No api keys, tokens, account numbers,
   private hostnames, names or customer data anywhere.
8. **Don't reimplement the chat.** A missing feature belongs in
   `../smarter-chat`'s embed script first.

## Repository map

The repository root is the module, as Composer installs it into
`modules/contrib/smarter_chat`.

| Path                                        | Contents                                                          |
| ------------------------------------------- | ----------------------------------------------------------------- |
| `smarter_chat.info.yml`, `*.yml`            | The module's metadata, routes, menu link, permission, libraries   |
| `smarter_chat.module`                       | Hooks: help, page attachments (the launcher), library alter       |
| `config/install/`, `config/schema/`         | The settings' defaults and schema                                 |
| `src/Validation.php`, `src/EmbedConfig.php` | Pure classes: validators, settings → embed options                |
| `src/Form/SettingsForm.php`                 | The settings form                                                 |
| `src/Plugin/Block/SmarterChatBlock.php`     | The Smarter Chat block                                            |
| `js/smarter-chat.config.js`                 | Copies `drupalSettings.smarterChat` to `window.SmarterChatConfig` |
| `tests/src/Unit/`                           | PHPUnit unit tests                                                |
| `tests/smoke/`                              | Docker Drupal smoke test                                          |
| `doc/adr/`, `skills/`                       | Architecture decisions, Agent Skills                              |

User documentation on docs.smarter.sh lives in the platform's repository:
`../smarter/docs/source/smarter-framework/smarter-drupal.rst`.

## Common commands

```bash
make init        # composer dev dependencies (Docker if php isn't installed), npm, pre-commit
make test        # PHPUnit unit tests
make coverage    # with coverage
make lint        # PHPCS: Drupal and DrupalPractice
make smoke       # Drupal 11 + SQLite in Docker: install, enable, configure, check pages
git diff --name-only | xargs pre-commit run --files
```

## Definition of done

- The change follows an existing pattern and the ADRs in `doc/adr/`.
- Tests cover it, you ran them, and the smoke test passes when Drupal-facing code changed.
- PHPCS and the pre-commit hooks pass on the changed files.
- `README.md`, `hook_help()` and docs.smarter.sh describe user-facing changes.
- Your summary says what changed, what you verified, and what you skipped.
