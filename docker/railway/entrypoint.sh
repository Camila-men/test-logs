#!/bin/sh
set -e

cd /app

# Railway передаёт порт через $PORT — FrankenPHP слушает адрес из SERVER_NAME.
export SERVER_NAME=":${PORT:-8080}"

echo "==> doctrine:migrations:migrate"
php bin/console doctrine:migrations:migrate --no-interaction || echo "Миграции не применились (БД ещё не готова?) — сервер всё равно запускается"

# Без аргументов передаётся дефолтный CMD базового образа dunglas/frankenphp,
# который и запускает Caddy/FrankenPHP на public/index.php.
exec "$@"
