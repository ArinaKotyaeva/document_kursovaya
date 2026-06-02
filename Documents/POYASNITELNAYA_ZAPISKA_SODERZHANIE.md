# Черновик пояснительной записки

## 3. Архитектура

Клиент-серверное приложение: **React** (представление) + **PHP** (REST API, слои router / middleware / controller / service) + **PostgreSQL** (данные). Авторизация JWT. Документация API — Swagger UI. Логирование запросов в таблицу `api_request_logs`.

## 5. Описание серверной части

Сервер реализован на **PHP** в каталоге `Source/backend/`. Точка входа `index.php`. Middleware: AuthMiddleware (JWT), RoleMiddleware, ValidationMiddleware, LoggingMiddleware. Контроллеры: AuthController, UserController, ReportController, AppUserController. Сервисы работают через PDO с PostgreSQL, часть операций — через хранимые процедуры (`sp_registry_soft_delete`, `sp_report_register_export`).

Диаграмма классов: перечислить классы из папок `controllers/`, `services/`, `middleware/`, `models/`.

## 6. Описание клиентской части

React-приложение (`Source/frontend/`): экран входа, реестр, форма добавления для admin, удаление, экспорт отчёта, LocalStorage `cachedUsers`.

Остальные разделы — см. `README.md` в корне проекта.
