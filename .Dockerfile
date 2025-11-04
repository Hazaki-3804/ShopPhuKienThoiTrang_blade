# Sử dụng PHP 8.2 chính thức
FROM php:8.2-fpm

# Cài đặt thư viện hệ thống & PHP extensions
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
 && docker-php-ext-install -j$(nproc) gd pdo_mysql zip \
 && docker-php-ext-enable gd \
 && rm -rf /var/lib/apt/lists/*

# Kiểm tra extension gd có được cài chưa
RUN php -m | grep -i gd

# Copy Composer từ image chính thức
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Thiết lập môi trường Composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Tạo thư mục làm việc
WORKDIR /var/www/html

# Copy toàn bộ source trước (chứ không chỉ composer.json)
# để đảm bảo các package có thể đọc cấu trúc project khi build
COPY . .

# Cài đặt dependencies PHP
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# Phân quyền cho Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Tạo link storage
RUN php artisan storage:link

# Mở port 8000 và chạy Laravel
EXPOSE 8080
CMD php artisan serve --host=0.0.0.0 --port=8080
