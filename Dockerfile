# Use the official PHP 8.4 image with Apache
FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/nextgold nextgold
RUN mkdir -p /home/nextgold/.composer && \
    chown -R nextgold:nextgold /home/nextgold

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=nextgold:nextgold . /var/www

# Set permissions
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Change current user to nextgold
USER nextgold

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
