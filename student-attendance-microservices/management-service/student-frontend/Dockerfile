FROM php:8.1.23-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql opcache

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .


