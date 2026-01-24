# ---- Build stage (Composer) ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

# ---- Runtime stage (PHP + Apache) ----
FROM php:8.2-apache

# System deps + PHP extensions commonly needed by Laravel
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql zip gd exif \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

# Set Apache docroot to /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copy app source
COPY . .

# Copy vendor from build stage
COPY --from=vendor /app/vendor ./vendor

# Permissions for Laravel storage/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Cloud Run uses PORT
ENV PORT=8080
EXPOSE 8080

# Ensure Apache listens on 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
 && sed -i 's/:80/:8080/' /etc/apache2/sites-available/000-default.conf

CMD ["apache2-foreground"]
