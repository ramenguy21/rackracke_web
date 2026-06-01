FROM php:8.4-cli-alpine

# All system libs required by the PHP extensions we install below
RUN apk add --no-cache \
    # pdo_pgsql / pgsql
    postgresql-dev \
    # mbstring
    oniguruma-dev \
    # gd
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    # zip
    libzip-dev \
    zip \
    unzip \
    # Node / npm for Vite
    nodejs \
    npm \
    # misc
    curl

# Build and install PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        mbstring \
        zip \
        gd \
        bcmath

# Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# PHP deps (layer-cached separately from source)
COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
        --no-dev \
        --optimize-autoloader \
        --no-scripts

# JS deps
COPY package.json package-lock.json ./
RUN npm ci

# Copy source and build
COPY . .

RUN npm run build \
    && COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Storage dirs
RUN mkdir -p storage/framework/{sessions,views,cache/data} \
             bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD php artisan migrate --force \
    && php artisan storage:link --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan serve --host=0.0.0.0 --port=8080
