# ADR-003: The Embed Script Is an External Library

## Status

Accepted

Differs from Smarter Chat for WordPress's [ADR-003](https://github.com/smarter-sh/smarter-wordpress/blob/main/doc/adr/003-vendored-embed.md), which ships the script in the plugin.

## Context

Drupal.org doesn't allow third-party code in a project's repository, and expects front-end libraries to be loaded from a CDN or installed separately. Drupal's library system supports external libraries (`type: external`) natively. `embed.js` loads its own files (Mermaid's chunks, KaTeX's fonts) from beside itself, wherever it is served from.

## Decision

- `smarter_chat.libraries.yml` declares `smarter_chat/embed` as an external library: `https://cdn.jsdelivr.net/npm/@smarter.sh/ui-chat@<EMBED_VERSION>/dist/embed.js`, `async`, not aggregated.
- A setting, **Script url**, replaces it, through `hook_library_info_alter()`, for sites that host `embed.js` and `embed/` themselves, e.g. in `/libraries/smarter-chat/`. Saving the settings invalidates the `library_info` cache tag.

## Alternatives Considered

- **Ship the script in the module**, as the WordPress plugin does. Not allowed on Drupal.org.
- **A Composer library package (asset-packagist).** Adds a repository that many sites don't use, for one file.

## Consequences

- **Positive:**
  - Acceptable on Drupal.org; nothing to build for a release.
- **Negative:**
  - By default, visitors' browsers download the script from jsDelivr, a third party, which the site's privacy notice and Content Security Policy must allow, unless the site hosts it.

## Related ADRs

- [ADR-002: The Embed Script Is the Chat](002-embed-script.md)
- [ADR-007: Caching](007-caching.md)
