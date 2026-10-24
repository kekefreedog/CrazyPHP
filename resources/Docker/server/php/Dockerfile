# Build the selected PHP server with the project's database extensions.
FROM {{base}}
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions {{extensions}}
# Match HTTP workers to the host user that creates application files and caches.
ARG CRAZY_UID=1000
ARG CRAZY_GID=1000
RUN groupmod -o -g "$CRAZY_GID" www-data && usermod -o -u "$CRAZY_UID" -g www-data www-data
{{apache}}
{{frankenphp}}
