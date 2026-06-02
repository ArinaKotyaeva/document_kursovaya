# Курсовая работа «Документ»

**Бэкенд: PHP** (без фреймворков, слои по ТЗ).  
**Фронтенд: React** (Vite).  
**БД: PostgreSQL** (таблицы, views, functions, procedures, triggers).

---

## Что не хватало по требованиям КР — и что сделано

| Требование | Решение | Где |
|------------|---------|-----|
| PostgreSQL, ≥4 таблиц | 6 таблиц | `DB/01_schema.sql` |
| ≥3 views, functions, procedures, triggers | по 3+ каждого | `DB/02` … `05` |
| CRUD ≥2 сущностей | реестр + пользователи системы | `/users`, `/app-users` |
| **Бэкенд на PHP** | основной API | `Source/backend/` |
| Авторизация | JWT `POST /auth/login` | `AuthService.php`, `AuthMiddleware.php` |
| Документация API (Swagger) | UI + OpenAPI | `/api/docs`, `openapi.json` |
| Логирование эндпоинтов | таблица `api_request_logs` | `LoggingMiddleware.php` |
| React (клиент) | SPA | `Source/frontend/` |
| Сдача: DB, Documents, Source | папки в корне | `DB/`, `Documents/`, `Source/` |

NestJS-черновик перенесён в `Source/_archive_nestjs/` и **не используется**.

---

## Структура проекта

```
kursovaya/
├── DB/                      SQL, бэкап PostgreSQL
├── Documents/               пояснительная записка (.docx)
├── Source/
│   ├── backend/             PHP API (основной бэкенд)
│   ├── frontend/            React
│   ├── legacy-php/          первая учебная PHP-версия (JSON-файл)
│   └── _archive_nestjs/     не используется
└── README.md
```

### PHP-бэкенд (`Source/backend/`)

| Путь | Назначение |
|------|------------|
| `index.php` | Роутер, CORS, маршруты |
| `bootstrap.php` | Загрузка `.env` |
| `config/Database.php` | PDO → PostgreSQL |
| `config/AuthContext.php` | Текущий пользователь после JWT |
| `middleware/AuthMiddleware.php` | Проверка Bearer JWT |
| `middleware/RoleMiddleware.php` | admin / guest |
| `middleware/ValidationMiddleware.php` | Валидация POST |
| `middleware/LoggingMiddleware.php` | Запись в `api_request_logs` |
| `controllers/*.php` | HTTP-ответы |
| `services/*.php` | Бизнес-логика |
| `models/User.php` | Учётная карточка реестра |
| `openapi.json` + `docs/swagger.html` | Swagger UI |

---

## Запуск (XAMPP)

### 1. PostgreSQL

Выполнить скрипты из `DB/` (порядок в `DB/README.md`).

В `php.ini` XAMPP включить расширения: `extension=pdo_pgsql`, `extension=pgsql`.

### 2. PHP API

Скопировать проект в `C:\xampp\htdocs\kursovaya\`.

```text
C:\xampp\htdocs\kursovaya\Source\backend\.env   ← из .env.example
```

Запустить **Apache**. API:

`http://localhost/kursovaya/Source/backend/users` (с JWT)

**Swagger:** `http://localhost/kursovaya/Source/backend/api/docs`

### 3. React

```bash
cd Source/frontend
npm install
npm run dev
```

UI: смотрите **точный адрес в терминале** после `npm run dev` (обычно `http://127.0.0.1:5173/`).

Если `ERR_CONNECTION_REFUSED` на 5173 — порт занят. Освободите его:

```powershell
Get-NetTCPConnection -LocalPort 5173 -ErrorAction SilentlyContinue | ForEach-Object { Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue }
```

Затем снова `npm run dev`. Если Vite пишет `5174` — откройте **http://127.0.0.1:5174/**.

### Вход

| Email | Пароль | Роль |
|-------|--------|------|
| admin@document.local | admin123 | admin |
| guest@document.local | guest123 | guest |

Если при входе **Invalid credentials** — один раз откройте в браузере:

`http://localhost/kursovaya/Source/backend/tools/install.php`

(создаст пользователей в PostgreSQL; нужны выполненные `DB/01_schema.sql` и запущенный PostgreSQL).

---

## Как протестировать всё по шагам

| Шаг | Что проверить | Ожидание |
|-----|---------------|----------|
| 1 | PostgreSQL запущен, база `document_kursovaya` создана | pgAdmin / служба PostgreSQL |
| 2 | Выполнены `DB/01` … `06` SQL или `tools/install.php` | Пользователи в БД |
| 3 | Apache Start, файл `Source/backend/.env` | — |
| 4 | `http://localhost/kursovaya/Source/backend/tools/install.php` | «Подключение OK», 2 пользователя |
| 5 | `http://localhost/kursovaya/Source/backend/api/docs` | Swagger UI |
| 6 | `npm run dev` в `Source/frontend` | `http://127.0.0.1:5173/` |
| 7 | Вход admin@document.local / admin123 | Реестр, форма добавления |
| 8 | Выйти → guest@document.local / guest123 | Без формы, только просмотр |
| 9 | «Скачать отчёт» | JSON-файл с записями |
| 10 | Добавить и удалить запись (admin) | Таблица обновляется |

---

## API (кратко)

| Метод | Путь | Auth | Описание |
|-------|------|------|----------|
| POST | `/auth/login` | — | JWT |
| GET | `/users` | JWT | Список реестра |
| POST | `/users` | JWT admin | Создать |
| DELETE | `/users/{id}` | JWT admin | Удалить (процедура БД) |
| GET | `/reports/export` | JWT | Отчёт JSON |
| GET | `/app-users` | JWT admin | Пользователи системы |

Полное описание — в Swagger.

---

## Архитектура

```
React (frontend)  --JWT-->  PHP index.php  --PDO-->  PostgreSQL
                                |
                                +-- Swagger /api/docs
                                +-- LoggingMiddleware
```

Слои PHP (как в учебном ТЗ): **роутер → middleware → controller → service → БД**.

---

## Пояснительная записка

Черновик разделов: `Documents/POYASNITELNAYA_ZAPISKA_SODERZHANIE.md`  
Оформить в Word по ГОСТ 7.32-2017, положить в `Documents/`.

---

## Сдача

Каталог: `БИВТ-24-1_Фамилия_ИО_вариант_Документ/`

- `DB/` — `pg_dump` бэкап  
- `Documents/` — .docx  
- `Source/` — код PHP + React  

---

*Дисциплина «Системы». Бэкенд: PHP. Клиент: React. СУБД: PostgreSQL.*
# document_kursovaya
# document_kursovaya
