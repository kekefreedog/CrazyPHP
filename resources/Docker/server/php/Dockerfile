# Build the selected PHP server with the project's database extensions.
FROM {{base}}
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions {{extensions}}
{{apache}}
