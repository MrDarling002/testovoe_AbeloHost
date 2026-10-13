FROM php:8.2-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends libonig-dev unzip \
 && docker-php-ext-install pdo_mysql mbstring opcache \
 && apt-get purge -y libonig-dev \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN mkdir -p /var/www/html/storage/cache/compiled \
             /var/www/html/storage/logs \
 && chown -R www-data:www-data /var/www/html/storage

EXPOSE 80