FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl \
    && docker-php-ext-install pdo_mysql

# Установим Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Устанавливаем зависимости (внутри контейнера)
RUN composer install || true

EXPOSE 9000
CMD ["php-fpm"]
