# Local development image: PHP + Apache, so .htaccess (clean URLs, the
# includes/ and uploads/ protection) behaves like it does on one.com.
FROM php:8.3-apache

# PHP extensions the site uses: pdo_mysql (recipes), gd (photo resizing),
# exif (turning photos the right way up).
RUN apt-get update \
    && apt-get install -y --no-install-recommends libjpeg62-turbo-dev libpng-dev libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd exif \
    && rm -rf /var/lib/apt/lists/*

# mail() is delivered to the Mailpit container instead of the real world.
COPY --from=axllent/mailpit:v1.27 /mailpit /usr/local/bin/mailpit

RUN a2enmod rewrite headers expires deflate
COPY docker/apache.conf /etc/apache2/conf-enabled/zz-homepage.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-homepage.ini

COPY public/ /var/www/html/
