CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    code VARCHAR(32) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE app_users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role_id INTEGER NOT NULL REFERENCES roles(id),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE registry_records (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    age INTEGER CHECK (age IS NULL OR age > 0),
    created_by INTEGER REFERENCES app_users(id),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE report_exports (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES app_users(id),
    format VARCHAR(10) NOT NULL CHECK (format IN ('json', 'csv')),
    record_count INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE api_request_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES app_users(id),
    method VARCHAR(10) NOT NULL,
    path TEXT NOT NULL,
    status_code INTEGER NOT NULL,
    duration_ms INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE registry_audit (
    id BIGSERIAL PRIMARY KEY,
    registry_id INTEGER NOT NULL,
    action VARCHAR(20) NOT NULL,
    changed_by INTEGER REFERENCES app_users(id),
    payload JSONB,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_registry_records_active ON registry_records (is_deleted) WHERE is_deleted = FALSE;
CREATE INDEX idx_api_request_logs_created_at ON api_request_logs (created_at DESC);
CREATE INDEX idx_report_exports_user_id ON report_exports (user_id);
