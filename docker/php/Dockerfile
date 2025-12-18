FROM php:8.1-fpm

COPY php.ini /usr/local/etc/php/

RUN apt update \
    && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip git \
    && docker-php-ext-install pdo_mysql zip \
    && apt clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer self-update

WORKDIR /var/www

RUN groupadd -g 1000 nishida\
    && useradd -m -u 1000 -g 1000 nishida
USER nishida