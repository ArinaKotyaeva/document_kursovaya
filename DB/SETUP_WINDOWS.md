# Установка PostgreSQL на Windows (для курсовой)

Ошибка `Invalid credentials` на сайте почти всегда из‑за того, что **PHP не может подключиться к PostgreSQL** или **не созданы таблицы/пользователи**.

## Шаг 1. Установить PostgreSQL

1. Скачайте: https://www.postgresql.org/download/windows/
2. Установите (порт **5432**, пользователь **postgres**).
3. **Запомните пароль**, который задаёте при установке.

## Шаг 2. Запустить службу

`Win + R` → `services.msc` → найдите **postgresql** → состояние **Выполняется**.

## Шаг 3. Создать базу

Откройте **pgAdmin** → Databases → Create → имя: `document_kursovaya`.

## Шаг 4. Выполнить SQL

В pgAdmin: Query Tool на базе `document_kursovaya`, по очереди откройте и выполните:

1. `01_schema.sql`
2. `02_views.sql`
3. `03_functions.sql`
4. `04_procedures.sql`
5. `05_triggers.sql`
6. `06_seed.sql`

## Шаг 5. Настроить `.env`

Файл: `Source/backend/.env` (и копия в `C:\xampp\htdocs\kursovaya\...`, если работаете через XAMPP).

```env
DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=document_kursovaya
DB_USER=postgres
DB_PASSWORD=тот_пароль_что_задали_при_установке
```

## Шаг 6. PHP + Apache

`C:\xampp\php\php.ini` — должны быть раскомментированы:

```ini
extension=pdo_pgsql
extension=pgsql
```

Перезапустите **Apache** в XAMPP.

## Шаг 7. Проверка

1. http://localhost/kursovaya/Source/backend/tools/diagnose.php  
   Должно: `pdo_pgsql: включён`, `Подключение: OK`, таблицы перечислены.

2. http://localhost/kursovaya/Source/backend/tools/install.php  
   Должно: 2 пользователя.

3. React: вход `admin@document.local` / `admin123`
