FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install

CMD ["php-fpm"]