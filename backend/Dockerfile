FROM php:8.2-fpm

# Устанавливаем нужные пакеты и расширения
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl \
    && docker-php-ext-install pdo_mysql

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Создаём нужные папки (чтобы не было ошибки cache path)
RUN mkdir -p storage/framework/{cache,views,sessions} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Устанавливаем зависимости (если composer.json уже есть)
RUN composer install || true

EXPOSE 9000
CMD ["php-fpm"]
