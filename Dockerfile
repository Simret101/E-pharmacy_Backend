FROM php:8.2-cli

WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libcurl4-openssl-dev \
    supervisor \
    && docker-php-ext-install pdo pdo_mysql bcmath zip curl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Laravel permissions (optional but recommended)
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

# Add supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8000

# Start both php server and queue worker
CMD ["/usr/bin/supervisord"]
