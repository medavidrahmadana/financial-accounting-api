FROM php:8.2-fpm

# Install system dependencies & PHP extensions (GD, Zip, PDO MySQL)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nginx

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-gd

# Configure Nginx
COPY nginx.conf /etc/nginx/sites-available/default

# Expose port
EXPOSE 8080

CMD php artisan migrate --force && php artisan config:cache && php artisan route:cache && nginx -g 'daemon off;'
