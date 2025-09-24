FROM php:8.2-fpm

# Install system dependencies for PECL and Composer
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip

# Install Redis extension via PECL and enable it
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy composer.json and composer.lock if available
COPY composer.json ./

# Install PHP dependencies using Composer (if composer.json is present)
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# Copy application code
COPY . .

CMD ["php-fpm"]
