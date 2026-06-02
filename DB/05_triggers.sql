CREATE OR REPLACE FUNCTION trg_fn_registry_set_updated_at()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.updated_at := NOW();
    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_registry_set_updated_at
BEFORE UPDATE ON registry_records
FOR EACH ROW
EXECUTE PROCEDURE trg_fn_registry_set_updated_at();

CREATE OR REPLACE FUNCTION trg_fn_registry_audit()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        INSERT INTO registry_audit (registry_id, action, changed_by, payload)
        VALUES (NEW.id, 'INSERT', NEW.created_by, to_jsonb(NEW));
    ELSIF TG_OP = 'UPDATE' THEN
        INSERT INTO registry_audit (registry_id, action, changed_by, payload)
        VALUES (NEW.id, 'UPDATE', NEW.created_by, jsonb_build_object('old', to_jsonb(OLD), 'new', to_jsonb(NEW)));
    ELSIF TG_OP = 'DELETE' THEN
        INSERT INTO registry_audit (registry_id, action, changed_by, payload)
        VALUES (OLD.id, 'DELETE', OLD.created_by, to_jsonb(OLD));
    END IF;

    RETURN COALESCE(NEW, OLD);
END;
$$;

CREATE TRIGGER trg_registry_audit
AFTER INSERT OR UPDATE OR DELETE ON registry_records
FOR EACH ROW
EXECUTE PROCEDURE trg_fn_registry_audit();

CREATE OR REPLACE FUNCTION trg_fn_registry_validate_age()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    IF NEW.age IS NOT NULL AND NEW.age <= 0 THEN
        RAISE EXCEPTION 'Age must be positive';
    END IF;

    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_registry_validate_age
BEFORE INSERT OR UPDATE ON registry_records
FOR EACH ROW
EXECUTE PROCEDURE trg_fn_registry_validate_age();
