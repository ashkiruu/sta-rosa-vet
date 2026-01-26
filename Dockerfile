# =========================
# 1) Vendor stage (Composer with required PHP extensions)
# =========================
FROM php:8.2-cli AS vendor

# ✅ Increase upload limits for mobile photos (iOS/Android)
RUN { \
  echo "upload_max_filesize=25M"; \
  echo "post_max_size=30M"; \
  echo "max_file_uploads=20"; \
  echo "memory_limit=512M"; \
  echo "max_execution_time=180"; \
  echo "max_input_time=180"; \
} > /usr/local/etc/php/conf.d/uploads.ini


RUN apt-get update && apt-get install -y \
    git unzip \
    libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libicu-dev libonig-dev libxml2-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql zip gd exif intl bcmath mbstring xml opcache \
 && rm -rf /var/lib/apt/lists/*

RUN echo "LimitRequestBody 0" >> /etc/apache2/apache2.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts



# =========================
# 2) Frontend stage (Vite build)
# =========================
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY public ./public
RUN npm run build



# =========================
# 3) Runtime stage (Apache + PHP)
# =========================
FROM php:8.2-apache

ENV PORT=8080
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf \
 && sed -i 's/:80/:8080/g' /etc/apache2/sites-available/000-default.conf

# =========================
# SYSTEM DEPENDENCIES (OCR + IMAGE NORMALIZATION)
# =========================
RUN apt-get update && apt-get install -y \
    unzip \
    tesseract-ocr \
    imagemagick \
    libheif1 \
    libde265-0 \
    heif-gdk-pixbuf \
    libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libicu-dev libonig-dev libxml2-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql zip gd exif intl bcmath mbstring xml opcache \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

# Sanity check (won’t fail build if grep fails)
RUN magick -version && magick -list format | grep -i heic || true



# =========================
# Apache + Laravel config
# =========================
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf



# =========================
# App code
# =========================
WORKDIR /var/www/html

COPY . .

COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build



# =========================
# Safe defaults for Cloud Run
# =========================
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
    CACHE_STORE=array \
    SESSION_DRIVER=array \
    QUEUE_CONNECTION=sync \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/tmp/database.sqlite

RUN touch /tmp/database.sqlite



# =========================
# Laravel cleanup + permissions
# =========================
RUN php artisan config:clear --ansi \
 && php artisan cache:clear --ansi \
 && php artisan route:clear --ansi \
 && php artisan view:clear --ansi \
 && php artisan package:discover --ansi

RUN mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache



COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080
CMD ["apache2-foreground"]
