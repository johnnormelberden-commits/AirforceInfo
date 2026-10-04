# ============================================================
# STAGE 1: Install PHP dependencies
# ============================================================

FROM composer:2 AS dependencies

WORKDIR /app

COPY composer.json .

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader


# ============================================================
# STAGE 2: PHP + Apache
# ============================================================

FROM php:8.2-apache

# Install PHP extensions required for TiDB
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite
RUN a2enmod rewrite

# Application directory
WORKDIR /var/www/html

# Copy application
COPY . /var/www/html/

# Copy Composer vendor directory
COPY --from=dependencies /app/vendor /var/www/html/vendor

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80