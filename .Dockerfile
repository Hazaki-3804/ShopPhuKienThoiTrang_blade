# Sử dụng PHP 8.2 chính thức
FROM php:8.2-fpm

# Cài các thư viện cần thiết và build extensions (gd, pdo_mysql, zip)
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    git \
    unzip \
    ca-certificates \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" gd pdo pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

# Xác nhận GD đã được enable (fail sớm nếu thiếu)
RUN php -m | grep -i gd

# Cài composer từ image chính thức
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Biến môi trường cho composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy code vào container
WORKDIR /var/www/html
COPY . .

# Cài package PHP (production)
composer install --optimize-autoloader --no-scripts --no-interaction --ignore-platform-req=ext-gd

# Phân quyền cho Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Mở port 8000 và chạy Laravel
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
