CREATE OR REPLACE PROCEDURE sp_registry_create(
    p_name VARCHAR,
    p_email VARCHAR,
    p_age INTEGER,
    p_user_id INTEGER,
    OUT p_new_id INTEGER
)
LANGUAGE plpgsql
AS $$
BEGIN
    IF NOT fn_validate_registry_email(p_email) THEN
        RAISE EXCEPTION 'Invalid email format';
    END IF;

    IF LENGTH(TRIM(p_name)) < 1 OR LENGTH(TRIM(p_name)) > 100 THEN
        RAISE EXCEPTION 'Name length must be between 1 and 100';
    END IF;

    INSERT INTO registry_records (name, email, age, created_by)
    VALUES (TRIM(p_name), TRIM(p_email), p_age, p_user_id)
    RETURNING id INTO p_new_id;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_registry_soft_delete(
    p_registry_id INTEGER,
    p_user_id INTEGER
)
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE registry_records
    SET is_deleted = TRUE, updated_at = NOW()
    WHERE id = p_registry_id AND is_deleted = FALSE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Registry record not found';
    END IF;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_report_register_export(
    p_user_id INTEGER,
    p_format VARCHAR,
    p_record_count INTEGER
)
LANGUAGE plpgsql
AS $$
BEGIN
    INSERT INTO report_exports (user_id, format, record_count)
    VALUES (p_user_id, p_format, p_record_count);
END;
$$;
