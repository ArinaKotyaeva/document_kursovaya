<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/AuthContext.php';

class RoleMiddleware
{
    public static function requireRole(string $method, string $route): void
    {
        $role = AuthContext::role();

        if ($role === null) {
            self::respond(401, ['error' => 'Unauthorized']);
        }

        if ($role !== 'guest') {
            return;
        }

        $forbidden = ($method === 'POST' && $route === '/users')
            || ($method === 'PATCH' && $route === '/users/{id}')
            || ($method === 'DELETE' && $route === '/users/{id}')
            || str_starts_with($route, '/app-users');

        if ($forbidden) {
            self::respond(403, ['error' => 'Access denied']);
        }
    }

    public static function requireAdmin(): void
    {
        if (AuthContext::role() !== 'admin') {
            self::respond(403, ['error' => 'Access denied']);
        }
    }

    private static function respond(int $code, array $payload): void
    {
        http_response_code($code);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
