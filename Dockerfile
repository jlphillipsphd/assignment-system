FROM php:8-apache
RUN apt-get update && apt-get install -y \
        libzip-dev && apt-get clean \
    && docker-php-ext-install -j$(nproc) zip \
    && a2enmod alias

ADD src /var/www/html
