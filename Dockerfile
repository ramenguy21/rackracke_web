FROM serversideup/php:8.4-cli

USER root
RUN apt-get update && apt-get install -y nodejs npm && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# PHP deps
COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
        --no-dev \
        --optimize-autoloader \
        --no-scripts \
        --ignore-platform-reqs \
        --no-interaction \
        --prefer-dist

# JS deps + build
COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN npm run build \
    && COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize --ignore-platform-reqs \
    && php artisan package:discover --ansi

RUN mkdir -p storage/framework/{sessions,views,cache/data} bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
