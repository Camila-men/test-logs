#!/bin/sh
set -e

cd /app

# Railway передаёт порт через $PORT — FrankenPHP слушает адрес из SERVER_NAME.
export SERVER_NAME=":${PORT:-8080}"

echo "==> doctrine:migrations:migrate"
php bin/console doctrine:migrations:migrate --no-interaction || echo "Миграции не применились (БД ещё не готова?) — сервер всё равно запускается"

# Явно запускаем FrankenPHP, а не полагаемся на унаследованный CMD — мы
# перезаписали ENTRYPOINT базового образа, а в dunglas/frankenphp именно в
# ENTRYPOINT (а не в CMD) прописан запуск сервера, так что "$@" был бы пустым.
exec frankenphp run --config /etc/frankenphp/Caddyfile
