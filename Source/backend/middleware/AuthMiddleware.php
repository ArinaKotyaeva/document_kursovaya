<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/AuthContext.php';
require_once __DIR__ . '/../services/JwtService.php';

class AuthMiddleware
{
    public static function handle(): void
    {
        $token = self::extractBearerToken();

        if ($token === null) {
            self::respond(401, ['error' => 'Unauthorized: missing Bearer token']);
        }

        $payload = JwtService::decode($token);

        if ($payload === null || !isset($payload['sub'], $payload['role'])) {
            self::respond(401, ['error' => 'Unauthorized: invalid or expired token']);
        }

        AuthContext::set([
            'id' => (int) $payload['sub'],
            'email' => (string) ($payload['email'] ?? ''),
            'role' => (string) $payload['role'],
            'fullName' => (string) ($payload['fullName'] ?? ''),
        ]);
    }

    private static function extractBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if ($header === '' && function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (strcasecmp($name, 'Authorization') === 0) {
                    $header = $value;
                    break;
                }
            }
        }

        if (preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private static function respond(int $code, array $payload): void
    {
        http_response_code($code);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
