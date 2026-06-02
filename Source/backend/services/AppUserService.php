<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class AppUserService
{
    public function getAll(): array
    {
        $stmt = Database::connection()->query(
            'SELECT u.id, u.email, u.full_name, u.is_active, r.code AS role, u.created_at
             FROM app_users u JOIN roles r ON r.id = u.role_id ORDER BY u.id'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT u.id, u.email, u.full_name, u.is_active, r.code AS role
             FROM app_users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $payload): array
    {
        $pdo = Database::connection();
        $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE code = :code');
        $roleStmt->execute(['code' => $payload['roleCode']]);
        $roleId = $roleStmt->fetchColumn();

        if (!$roleId) {
            throw new RuntimeException('Role not found');
        }

        $hashStmt = $pdo->prepare('SELECT crypt(:password, gen_salt(\'bf\')) AS hash');
        $hashStmt->execute(['password' => $payload['password']]);
        $hash = $hashStmt->fetchColumn();

        $stmt = $pdo->prepare(
            'INSERT INTO app_users (email, password_hash, full_name, role_id)
             VALUES (:email, :hash, :full_name, :role_id)
             RETURNING id'
        );
        $stmt->execute([
            'email' => $payload['email'],
            'hash' => $hash,
            'full_name' => $payload['fullName'],
            'role_id' => $roleId,
        ]);

        return $this->findById((int) $stmt->fetchColumn());
    }

    public function deactivate(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'UPDATE app_users SET is_active = FALSE WHERE id = :id RETURNING id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetchColumn() ? $this->findById($id) : null;
    }
}
