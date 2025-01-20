# Extend the base PHP CLI image
FROM php:8.3-cli

# Install required system packages (e.g., git, unzip)
RUN apt-get update; \ 
    apt-get -y --no-install-recommends install \
        git \
        unzip \
        php8.3-mysql \ 
        php8.3-gd \ 
        php8.3-exif; \ 
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install and enable the pcntl extension
RUN docker-php-ext-install pcntl

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
