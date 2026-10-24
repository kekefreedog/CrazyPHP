# CrazyPHP CLI runtime: PHP, Composer and Node for hosts without local tools.

# PHP version of the project, passed by the CLI bootstrap; declared before the first FROM
ARG PHP_VERSION=8.3

FROM node:22-bookworm-slim AS node
FROM composer:2 AS composer
FROM docker:29-cli AS docker
FROM php:${PHP_VERSION}-cli-bookworm

# Install the extensions used by the framework and optional database drivers.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev libicu-dev libonig-dev libpq-dev libssl-dev \
    && docker-php-ext-install -j"$(nproc)" bcmath intl mbstring pdo_mysql pdo_pgsql zip pcntl \
    && MAKEFLAGS="-j$(nproc)" pecl install mongodb-1.21.12 \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

# Use the same toolchain for installation and frontend compilation.
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=docker /usr/local/bin/docker /usr/local/bin/docker
COPY --from=docker /usr/local/libexec/docker/cli-plugins /usr/local/libexec/docker/cli-plugins
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx
ENV HOME=/tmp
