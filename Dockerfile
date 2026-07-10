# Продакшн-сборка специально для Railway (или любого PaaS без docker-compose).
# Локальная разработка идёт через docker-compose.yml + docker/php/Dockerfile
# (nginx + php-fpm раздельно) — этот файл их не затрагивает.
#
# FrankenPHP — official-образ, который "из коробки" рассчитан на структуру
# Symfony/Laravel (root = /app/public), поэтому кастомный Caddyfile не нужен.
FROM dunglas/frankenphp:php8.3-bookworm

RUN install-php-extensions pdo_mysql intl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

COPY . .
RUN composer dump-autoload --no-dev --optimize \
    && mkdir -p var/cache var/log

COPY docker/railway/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
