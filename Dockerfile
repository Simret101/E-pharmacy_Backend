FROM php:8.2-cli

WORKDIR /var/www

COPY . .

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_mysql bcmath zip curl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 8000

# Run migrations first, then start services
CMD ["sh", "-c", "php artisan migrate:fresh --force && php artisan cache:clear && php artisan config:clear && php artisan serve --host=0.0.0.0 --port=8000 & php artisan queue:work"]