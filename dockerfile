FROM php:8.4-fpm

# Cài đặt system dependencies & PHP extensions (GD, Zip, MySQL)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo_mysql bcmath

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Cài đặt thư viện PHP
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Cấp quyền cho thư mục runtime
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Chạy PHP-FPM; migrate/cache/generate docs nên chạy ở bước deploy riêng
CMD ["php-fpm"]