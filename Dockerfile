FROM php:8.3-apache

RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY apache.conf /etc/apache2/conf-available/heritaxaqr.conf
RUN a2enconf heritaxaqr

COPY php.ini /usr/local/etc/php/conf.d/uploads.ini

COPY . /var/www/html/

RUN mkdir -p /var/www/html/data /var/www/html/assets/uploads \
    && chown -R www-data:www-data /var/www/html/data /var/www/html/assets/uploads

EXPOSE 80
