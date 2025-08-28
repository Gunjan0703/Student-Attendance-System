FROM php:8.1-apache

# Enable Apache mods
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy source code
#COPY . /var/www/html/
COPY --chown=www-data:www-data . /var/www/html/

EXPOSE 80
