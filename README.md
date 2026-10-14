# Smarter Chat for Drupal

[![Test](https://github.com/smarter-sh/smarter-drupal/actions/workflows/test.yml/badge.svg?branch=main)](https://github.com/smarter-sh/smarter-drupal/actions/workflows/test.yml)
[![License: GPL v2 or later](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)](LICENSE.txt)

## Introduction

Smarter Chat adds a [Smarter](https://smarter.sh) chatbot to a Drupal site: a chat launcher in a corner of every page,
and a Smarter Chat block that puts the chat in any region. The chatbot is an LLMClient that you build and deploy on a
Smarter platform; its look (logo, colors, fonts) comes from its manifest. The chat is the embed script of
[`@smarter.sh/ui-chat`](https://github.com/smarter-sh/smarter-chat), which renders it in a Shadow DOM, so the theme
can't break it.

- [User guide](https://docs.smarter.sh/en/latest/smarter-framework/smarter-drupal.html)
- [Issues](https://github.com/smarter-sh/smarter-drupal/issues)
- [Architecture decisions](doc/adr/000-intro.md)

## Requirements

- Drupal 10.3 or 11, PHP 8.1 or later.
- A deployed LLMClient on a Smarter platform, whose manifest allows the site's origin:

  ```yaml
  spec:
    config:
      prompt:
        allowedOrigins:
          - https://www.example.com
  ```

## Installation

Add this repository to the site's `composer.json`, then require the module and enable it:

```console
composer config repositories.smarter-drupal vcs https://github.com/smarter-sh/smarter-drupal
composer require smarter-sh/smarter-drupal
drush pm:install smarter_chat
```

## Configuration

1. Grant **Administer Smarter Chat** to the roles that may set the chatbot.
2. In **Configuration → Web services → Smarter Chat** (`/admin/config/services/smarter-chat`), enter the deployed
   LLMClient's url. The form shows the origin to allow in the manifest.
3. The launcher appears on every page that isn't an admin page. To put the chat in a region instead, turn off **Show on
   every page**, and place the **Smarter Chat** block in Block layout or Layout Builder.

An api key, if the LLMClient needs one, is public in the page, and is exported with the configuration. Set it in
`settings.php` instead:

```php
$config['smarter_chat.settings']['api_key'] = getenv('SMARTER_API_KEY');
```

By default, visitors' browsers download the chat's script from jsDelivr. To host it yourself, copy `dist/embed.js` and
`dist/embed/` of `@smarter.sh/ui-chat` together, e.g. to `/libraries/smarter-chat/`, and set **Advanced → Script url**.

Modules can change the launcher's options, or hide it on a page, with `hook_smarter_chat_launcher_alter()`. See
`smarter_chat.api.php`.

## Development

PHP isn't required: the PHP targets run in Docker when `php` isn't installed.

```console
make init        # composer and npm dev dependencies, pre-commit hooks
make test        # unit tests
make lint        # Drupal coding standards
make smoke       # Drupal 11 in Docker: install, enable, configure, check pages (DRUPAL_IMAGE=drupal:10.3 for 10.3)
```

The repository is developed with [Agent Skills](skills/README.md) for AI coding agents; start with
[CLAUDE.md](CLAUDE.md). See [CONTRIBUTING.md](.github/CONTRIBUTING.md).

## Maintainers

- [Lawrence McDaniel](https://github.com/lpm0073)
