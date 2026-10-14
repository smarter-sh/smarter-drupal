---
name: monthly-maintenance
description: Use when asked to do smarter-drupal's monthly maintenance, or part of it - bump EmbedConfig::EMBED_VERSION and the external library url to the latest @smarter.sh/ui-chat and pick up its new options, check the current Drupal core release with the smoke test, update composer and npm dev dependencies, retest, and write a "fix" or "feat" git commit.
---

# Monthly Maintenance

1. **The embed script.** Compare `EmbedConfig::EMBED_VERSION` (and the url in
   `smarter_chat.libraries.yml`, which must match; a unit test checks) with
   `npm view @smarter.sh/ui-chat version`. Read `../smarter-chat/CHANGELOG.md`
   between them, especially `src/embed/options.ts`. Update both, and add
   settings for new options (see "Adding an option" in
   `smarter-drupal-development`). Minor version: `feat`; patch: `fix`.
2. **Drupal.** Run `make smoke` (the `drupal:11` image is the latest 11.x),
   and `DRUPAL_IMAGE=drupal:10.3 make smoke` for the oldest supported release.
3. **Dependencies.** `composer update` (in Docker if needed), `npm update`,
   `pre-commit autoupdate`.
4. **Test.** `make lint test smoke`.
5. **Commit.** Load `smarter-git-commits`, and name the embed version tested.
