# Блог на чистом PHP + Smarty + MySQL

Без PHP-фреймворков и ORM.

## Стек

| Компонент | Версия |
|-----------|--------|
| PHP | 8.1+ (Docker: 8.2) |
| MySQL | 8.0 |
| Smarty | 4.x |
| Docker / Compose | локальный запуск |

## Предварительное условие

Dockerfile требует `composer.lock`. Для генерации нужен только Docker:

```bash
make lock
git add composer.lock && git commit -m "Add composer.lock"