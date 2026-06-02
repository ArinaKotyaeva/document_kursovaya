CREATE OR REPLACE FUNCTION fn_registry_count_active()
RETURNS INTEGER
LANGUAGE sql
STABLE
AS $$
    SELECT COUNT(*)::INTEGER FROM registry_records WHERE is_deleted = FALSE;
$$;

CREATE OR REPLACE FUNCTION fn_user_has_role(p_user_id INTEGER, p_role_code VARCHAR)
RETURNS BOOLEAN
LANGUAGE plpgsql
STABLE
AS $$
DECLARE
    v_ok BOOLEAN;
BEGIN
    SELECT EXISTS (
        SELECT 1
        FROM app_users u
        JOIN roles r ON r.id = u.role_id
        WHERE u.id = p_user_id AND u.is_active = TRUE AND r.code = p_role_code
    ) INTO v_ok;

    RETURN COALESCE(v_ok, FALSE);
END;
$$;

CREATE OR REPLACE FUNCTION fn_validate_registry_email(p_email VARCHAR)
RETURNS BOOLEAN
LANGUAGE plpgsql
IMMUTABLE
AS $$
BEGIN
    RETURN p_email ~* '^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$';
END;
$$;
