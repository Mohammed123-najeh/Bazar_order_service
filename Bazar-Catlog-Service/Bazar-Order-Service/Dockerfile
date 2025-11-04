FROM php:8.2-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite \
    sqlite-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_sqlite mbstring

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock* ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application files
COPY . .

# Create database directory and file
RUN mkdir -p /app && touch /app/orders.db && chmod 777 /app/orders.db

# Set permissions
RUN chmod -R 777 /var/www/html && chmod -R 777 /app

# Expose port
EXPOSE 8080

# Run migrations and start server
CMD sh -c "php artisan migrate --force || true && php -S 0.0.0.0:8080 -t public public/index.php"

