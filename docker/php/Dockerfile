FROM php:7.4-apache

RUN apt-get update && apt-get install -y \
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
        gd

RUN a2enmod rewrite

# Configuration PHP pour Symfony 3.1 legacy
COPY docker/php/conf.d/symfony.ini /usr/local/etc/php/conf.d/symfony.ini

# Symfony 3 utilise le dossier web comme DocumentRoot
RUN sed -ri -e 's!/var/www/html!/var/www/html/web!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html