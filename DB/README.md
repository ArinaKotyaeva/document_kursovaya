# База данных PostgreSQL — «Документ»

## Порядок развёртывания

```bash
psql -U postgres -c "CREATE DATABASE document_kursovaya;"
psql -U postgres -d document_kursovaya -f 01_schema.sql
psql -U postgres -d document_kursovaya -f 02_views.sql
psql -U postgres -d document_kursovaya -f 03_functions.sql
psql -U postgres -d document_kursovaya -f 04_procedures.sql
psql -U postgres -d document_kursovaya -f 05_triggers.sql
psql -U postgres -d document_kursovaya -f 06_seed.sql
```

## Бэкап для сдачи (папка DB)

```bash
pg_dump -U postgres -Fc -f BIVT-24-1_Ivanov_IV_9_document.dump document_kursovaya
```

Имя файла замените на свою группу, ФИО и номер варианта латиницей.

## Объекты БД (требования КР)

| Тип | Количество | Файл |
|-----|------------|------|
| Таблицы | 6 | `01_schema.sql` |
| Представления | 3 | `02_views.sql` |
| Функции | 3 | `03_functions.sql` |
| Хранимые процедуры | 3 | `04_procedures.sql` |
| Триггеры | 3 | `05_triggers.sql` |

## Тестовые учётные записи

| Email | Пароль | Роль |
|-------|--------|------|
| admin@document.local | admin123 | admin |
| guest@document.local | guest123 | guest |
