FROM php:8.2-cli

RUN apt-get update -y && apt-get install -y libmariadb-dev unzip \
    && docker-php-ext-install pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html