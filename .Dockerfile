# Sử dụng PHP 8.2 chính thức
FROM php:8.2-fpm

# Cài thư viện cần thiết cho GD, zip, PDO MySQL
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    zip \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Cài composer từ image chính thức
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy source code
WORKDIR /var/www/html
COPY . .

# Cài package PHP (không dev, tối ưu autoload)
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Phân quyền cho Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Mở port 8000 và chạy Laravel
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
