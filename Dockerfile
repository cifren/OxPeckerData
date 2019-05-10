FROM composer:latest

RUN apt-get update \
    && apt-get install -y autoconf

RUN pecl install xdebug-2.7.2 \
    && docker-php-ext-enable xdebug
