---
name: smarter-drupal-testing
description: Use when writing, fixing or running the smarter-drupal module's tests - the standalone PHPUnit unit tests of Validation and EmbedConfig, run in Docker when php isn't installed, and the Docker smoke test that installs Drupal 11 with Drush and SQLite, enables the module, configures it, places the block and checks pages with curl. Covers coverage, what each layer proves, and what never to do in a test.
---

# Smarter Chat for Drupal: Testing

## Unit tests

`tests/src/Unit/`, namespace `Drupal\Tests\smarter_chat\Unit`, one test class
per pure class (`ValidationTest`, `EmbedConfigTest`). They extend PHPUnit's
`TestCase`, not Drupal's `UnitTestCase`, so that they run with only this
repository's `vendor/` (Composer's `autoload-dev` maps the namespaces). Keep
Drupal out of `Validation` and `EmbedConfig` so that it stays that way.

Test hostile input for every validator: `javascript:` and `data:` urls,
credentials in urls, quotes, css injection (`red; background:url(...)`), and
non-string values.

`make test` uses the local `php` if there is one, else Docker
(`tests/docker/Dockerfile`, PHP 8.1 with PCOV). `make coverage` reports the
coverage of `src/Validation.php` and `src/EmbedConfig.php`.

## Smoke test

`make smoke` (`tests/smoke/run.sh`) runs the `drupal:11` image, adds Drush with
Composer, installs the `standard` profile on SQLite, mounts the working tree as
`modules/contrib/smarter_chat`, enables it, sets the configuration with
`drush config:set`, places the block in the content region with
`drush php:eval`, and checks with `curl`:

- the front page has `drupalSettings.smarterChat` (the launcher) and the async
  external script,
- a hostile label comes out escaped in the JSON,
- the block renders `<smarter-chat chatbot="..." height="..." width="...">`,
- an admin page has no launcher,
- turning the launcher off shows on the next request (cache tags work),
- `script_url` replaces the CDN url (library alter, `library_info` invalidated),
- the settings form is reachable for the admin user.

It removes its container at the end, even on failure, and never contacts a
Smarter platform (the chatbot is `https://chat.example.com/`). The first run
downloads the image and Drush (a few minutes); later runs reuse the image.

## Never

- Never point a test at a real Smarter platform or a real api key.
- Never leave the smoke test's container running.
