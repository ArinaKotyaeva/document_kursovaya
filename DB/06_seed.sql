INSERT INTO roles (code, name) VALUES
    ('admin', 'Администратор'),
    ('guest', 'Гость')
ON CONFLICT (code) DO NOTHING;

INSERT INTO app_users (email, password_hash, full_name, role_id)
SELECT
    'admin@document.local',
    crypt('admin123', gen_salt('bf')),
    'Администратор системы',
    r.id
FROM roles r WHERE r.code = 'admin'
ON CONFLICT (email) DO NOTHING;

INSERT INTO app_users (email, password_hash, full_name, role_id)
SELECT
    'guest@document.local',
    crypt('guest123', gen_salt('bf')),
    'Гость системы',
    r.id
FROM roles r WHERE r.code = 'guest'
ON CONFLICT (email) DO NOTHING;

INSERT INTO registry_records (name, email, age, created_by)
SELECT 'Иван Иванов', 'ivan@example.com', 22, u.id
FROM app_users u WHERE u.email = 'admin@document.local'
WHERE NOT EXISTS (SELECT 1 FROM registry_records WHERE email = 'ivan@example.com');

INSERT INTO registry_records (name, email, age, created_by)
SELECT 'Мария Петрова', 'maria@example.com', 19, u.id
FROM app_users u WHERE u.email = 'admin@document.local';

INSERT INTO registry_records (name, email, age, created_by)
SELECT 'Алексей Смирнов', 'alexey@example.com', NULL, u.id
FROM app_users u WHERE u.email = 'admin@document.local';

INSERT INTO report_exports (user_id, format, record_count)
SELECT u.id, 'json', fn_registry_count_active()
FROM app_users u WHERE u.email = 'admin@document.local';
