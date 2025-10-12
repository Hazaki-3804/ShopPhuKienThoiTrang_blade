# Sử dụng PHP 8.2 chính thức
FROM php:8.2-fpm

# Cài các thư viện cần thiết
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zip \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Cài composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy code vào container
WORKDIR /var/www/html
COPY . .

# Cài package PHP
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Phân quyền cho Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
