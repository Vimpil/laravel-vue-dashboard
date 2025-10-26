# Используем PHP 8.2 FPM
FROM php:8.2-fpm

# Устанавливаем нужные пакеты и расширения
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl \
    && docker-php-ext-install pdo_mysql

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем только файлы Composer, чтобы использовать кэш Docker
COPY composer.json composer.lock ./

# Устанавливаем зависимости
RUN composer install --no-dev --optimize-autoloader

# Копируем весь проект
COPY . .

# Создаём нужные папки для storage и bootstrap/cache
RUN mkdir -p storage/framework/{cache,views,sessions} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Открываем порт PHP-FPM
EXPOSE 9000

# Запуск PHP-FPM
CMD ["php-fpm"]
