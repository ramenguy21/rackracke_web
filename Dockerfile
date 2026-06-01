FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    postgresql-dev \
    oniguruma-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    curl

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

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
        --no-dev \
        --optimize-autoloader \
        --no-scripts \
        --ignore-platform-reqs \
        --no-interaction \
        --prefer-dist

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN npm run build \
    && COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize --ignore-platform-reqs \
    && php artisan package:discover --ansi

RUN mkdir -p storage/framework/{sessions,views,cache/data} \
             bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE ${PORT:-8080}

COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
