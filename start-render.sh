#!/bin/sh

set -e

echo "Running Laravel migrations..."

php artisan migrate --force

echo "Creating storage link..."

php artisan storage:link || true

echo "Caching Laravel configuration..."

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Laravel server..."

exec /start.sh