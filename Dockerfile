FROM php:8.2-fpm-alpine

WORKDIR /app

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    sqlite \
    && docker-php-ext-install pdo pdo_sqlite zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files and install dependencies
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage

# Expose port
EXPOSE 8080

# Use PHP built-in server for development/production
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
