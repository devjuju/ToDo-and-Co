FROM php:8.2-apache

# Optimiser l'installation des dépendances Docker
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        intl \
        zip \
        gd \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

# Configuration PHP application Symfony
COPY docker/php/conf.d/app.ini /usr/local/etc/php/conf.d/app.ini

# Symfony Flex utilise le dossier public comme DocumentRoot
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug