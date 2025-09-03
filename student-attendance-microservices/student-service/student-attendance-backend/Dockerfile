# Stage 1: Build the PHP application with all dependencies
FROM php:8.2-fpm-alpine AS php_build

# Install dependencies for building PHP extensions
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    mariadb-connector-c-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip

# Copy composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory and copy dependencies first for caching
WORKDIR /app
COPY composer.json composer.lock ./

# Install Composer dependencies
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy application source code
COPY . .

# Stage 2: Create a lightweight final image with Nginx
FROM nginx:stable-alpine

# Copy application code and PHP-FPM config from the build stage
# The 'php-fpm' executable is already available in the build stage, no need to install it again
COPY --from=php_build /app /var/www/html
COPY --from=php_build /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --from=php_build /usr/local/bin/php-fpm /usr/local/bin/php-fpm # Copy php-fpm executable

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html

EXPOSE 8000

# This entrypoint script ensures both Nginx and PHP-FPM run properly
CMD sh -c "php-fpm && nginx -g 'daemon off;'"
