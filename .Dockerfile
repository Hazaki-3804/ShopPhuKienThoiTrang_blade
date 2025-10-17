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
 && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip \
 && docker-php-ext-enable gd \
 && rm -rf /var/lib/apt/lists/*

# Kiểm tra lại GD có hoạt động chưa
RUN php -m | grep -i gd

# Cài composer từ image chính thức
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Biến môi trường cho composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Tạo thư mục làm việc
WORKDIR /var/www/html

# Copy file composer trước để cache layer dependencies
COPY composer.json composer.lock ./

# Cài package PHP (production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy toàn bộ mã nguồn còn lại
COPY . .

# Phân quyền cho Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Mở port 8000 và chạy Laravel
EXPOSE 8080
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
