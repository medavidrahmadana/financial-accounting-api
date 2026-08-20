FROM php:8.2-cli

# Install system dependencies & PHP extensions (GD, Zip, PDO MySQL)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-gd

# Expose port 8080
EXPOSE 8080

# Run migrations & start PHP built-in web server in foreground mode
CMD php artisan migrate --force && php artisan config:clear && php -S 0.0.0.0:8080 -t public
