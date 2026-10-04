FROM php:8.2-apache

# Install MySQL PDO driver for TiDB
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Application directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Install PHPMailer
RUN composer require phpmailer/phpmailer --no-interaction --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80