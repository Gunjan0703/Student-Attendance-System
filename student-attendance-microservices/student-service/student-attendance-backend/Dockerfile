# Use a more appropriate base image that includes common PHP extensions
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    # The libmariadb-dev-compat package is often needed for pdo_mysql on debian
    libmariadb-dev-compat \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Copy the composer binary from the official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application files
COPY . .

# Install composer dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Expose port and start the server. Using php-fpm is better for web servers.
EXPOSE 8000

# The FPM image automatically starts php-fpm. You can define a custom command if needed.
CMD ["php-fpm"]
