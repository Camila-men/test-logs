#!/bin/sh
set -e

cd /var/www/html

echo "==> composer install"
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "==> doctrine:migrations:migrate"
RETRIES=15
until php bin/console doctrine:migrations:migrate --no-interaction; do
    RETRIES=$((RETRIES - 1))
    if [ "$RETRIES" -le 0 ]; then
        echo "Database is not reachable, giving up on migrations." >&2
        break
    fi
    echo "Database not ready yet, retrying migrations in 2s... ($RETRIES left)"
    sleep 2
done

exec "$@"
