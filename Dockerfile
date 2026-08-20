FROM php:8.2-fpm

# Install system dependencies & PHP extensions (GD, Zip, SQLite, PDO MySQL)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev

RUN docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Create SQLite database file if not exists
RUN touch /var/www/database/database.sqlite && chmod 777 /var/www/database/database.sqlite

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-gd

# Expose port
EXPOSE 8080

# Run seeders & start persistent PHP web server
CMD ["sh", "-c", "php artisan migrate:fresh --seed --force && php -S 0.0.0.0:${PORT:-8080} -t public"]
