<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/JwtService.php';

class AuthService
{
    public function login(string $email, string $password): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT u.id, u.email, u.password_hash, u.full_name, r.code AS role
             FROM app_users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email AND u.is_active = TRUE'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new RuntimeException('Invalid credentials');
        }

        $check = $pdo->prepare('SELECT crypt(:password, :hash) = :hash AS ok');
        $check->execute(['password' => $password, 'hash' => $row['password_hash']]);
        $valid = (bool) $check->fetchColumn();

        if (!$valid) {
            throw new RuntimeException('Invalid credentials');
        }

        $token = JwtService::encode([
            'sub' => (int) $row['id'],
            'email' => $row['email'],
            'role' => $row['role'],
            'fullName' => $row['full_name'],
        ]);

        return [
            'accessToken' => $token,
            'user' => [
                'id' => (int) $row['id'],
                'email' => $row['email'],
                'fullName' => $row['full_name'],
                'role' => $row['role'],
            ],
        ];
    }
}
