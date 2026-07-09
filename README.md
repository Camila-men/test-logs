# Тестовое задание: Auth + логи (Symfony 6.4)

Регистрация/авторизация по email+паролю, журнал попыток входа/регистрации в MySQL,
защищённый список логов с пагинацией и фильтрами. Подробности и обоснование решений —
в [EXPLANATION.md](EXPLANATION.md).

## Запуск через Docker

Требуется только Docker и Docker Compose.

```bash
docker-compose up -d --build
```

Больше ничего вручную запускать не нужно: `docker/php/entrypoint.sh` при каждом
старте php-контейнера сам ставит зависимости (`composer install`) и применяет
миграции (`doctrine:migrations:migrate`), дожидаясь готовности БД. Оба действия
идемпотентны — повторный запуск ничего не ломает и не дублирует.

Сайт будет доступен на <http://localhost:8081>.

- `/register` — регистрация
- `/login` — вход
- `/logs` — список логов (только для авторизованных пользователей)

Прогресс установки/миграций можно посмотреть в логах:

```bash
docker-compose logs -f php
```

## Переменные окружения

`.env` уже настроен на подключение к сервису `database` из `docker-compose.yml`
(логин/пароль `app`/`app`, база `app`). Для запуска без Docker скопируйте
`.env.example` в `.env.local` и поправьте `DATABASE_URL` под свою MySQL.

## Полезные команды

```bash
# новая миграция после изменения сущностей
docker-compose exec php bin/console make:migration

# применить миграции
docker-compose exec php bin/console doctrine:migrations:migrate

# логи контейнеров
docker-compose logs -f php
```

