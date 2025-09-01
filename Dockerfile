# ------------------------
# Stage 1: Composer dependencies
# ------------------------
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./

# Install all dependencies INCLUDING dev so Pail exists
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .

RUN composer run-script post-autoload-dump

# ------------------------
# Stage 2: Node/Vite build
# ------------------------
FROM node:20 AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm ci --omit=dev

COPY . .

RUN npm run build

# ------------------------
# Stage 3: PHP-FPM + Nginx
# ------------------------
FROM php:8.2-fpm

# Install system packages
RUN apt-get update && apt-get install -y \
    nginx git unzip libpng-dev libonig-dev libxml2-dev zip curl \
    libpq-dev default-mysql-client supervisor \
    && docker-php-ext-install pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy Laravel app
COPY . .

# Copy vendor & built frontend assets from earlier stages
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public ./public

# RUN php artisan optimize:clear
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear
# Copy Nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf
RUN rm /etc/nginx/sites-enabled/default || true

# Supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
