FROM php:8.2-apache

# Install MySQL PDO driver for TiDB
RUN docker-php-ext-install pdo pdo_mysql

# Copy application files
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

# Enable Apache rewrite module
RUN a2enmod rewrite

EXPOSE 80
