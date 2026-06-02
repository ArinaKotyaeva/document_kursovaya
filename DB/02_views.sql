CREATE OR REPLACE VIEW v_registry_active AS
SELECT
    r.id,
    r.name,
    r.email,
    r.age,
    r.created_at,
    r.updated_at,
    u.full_name AS author_name,
    ro.code AS author_role
FROM registry_records r
LEFT JOIN app_users u ON u.id = r.created_by
LEFT JOIN roles ro ON ro.id = u.role_id
WHERE r.is_deleted = FALSE;

CREATE OR REPLACE VIEW v_user_roles AS
SELECT
    u.id,
    u.email,
    u.full_name,
    u.is_active,
    r.code AS role_code,
    r.name AS role_name,
    u.created_at
FROM app_users u
JOIN roles r ON r.id = u.role_id;

CREATE OR REPLACE VIEW v_export_statistics AS
SELECT
    u.id AS user_id,
    u.full_name,
    COUNT(e.id) AS exports_total,
    COALESCE(SUM(e.record_count), 0) AS records_exported,
    MAX(e.created_at) AS last_export_at
FROM app_users u
LEFT JOIN report_exports e ON e.user_id = u.id
GROUP BY u.id, u.full_name;
