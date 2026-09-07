#!/usr/bin/env bash
set -e

php artisan storage:link || true

for i in 1 2 3 4 5 6 7 8 9 10; do
    php artisan migrate --force && break
    echo "Database not ready yet, retrying in 3s... ($i/10)"
    sleep 3
done

if [ "$(php artisan tinker --execute='echo \App\Models\User::count();' 2>/dev/null | tail -n1)" = "0" ]; then
    php artisan db:seed --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@"
