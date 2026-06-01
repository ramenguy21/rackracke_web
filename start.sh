#!/bin/sh
set -e

echo "[start] migrate"
php artisan migrate --force

echo "[start] storage:link"
php artisan storage:link --force

echo "[start] config:cache"
php artisan config:cache

echo "[start] view:cache"
php artisan view:cache

echo "[start] serving on 0.0.0.0:8080"
php artisan serve --host=0.0.0.0 --port=8080
