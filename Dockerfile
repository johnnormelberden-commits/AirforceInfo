# ============================================================
# STAGE 1
# Install Composer dependencies
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
# STAGE 2
# PHP + Apache
# ============================================================

FROM php:8.2-apache

# TiDB / MySQL PDO support
RUN docker-php-ext-install pdo pdo_mysql

# Apache rewrite
RUN a2enmod rewrite

# Application directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Copy Composer dependencies
COPY --from=dependencies /app/vendor /var/www/html/vendor

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80