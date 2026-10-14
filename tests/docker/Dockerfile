# PHP for the unit tests and the linters, on a machine without PHP: `make test`, `make lint`, `make coverage`.
# PHP 8.1, the lowest version that the module supports (doc/adr/008-supported-versions.md), with PCOV for coverage.
ARG PHP_VERSION=8.1
FROM php:${PHP_VERSION}-cli-alpine
RUN apk add --no-cache git unzip $PHPIZE_DEPS \
  && pecl install pcov \
  && docker-php-ext-enable pcov \
  && apk del $PHPIZE_DEPS
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
