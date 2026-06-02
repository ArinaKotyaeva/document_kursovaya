<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/AuthService.php';

class AuthController
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    public function login(array $payload): void
    {
        try {
            if (!isset($payload['email'], $payload['password'])) {
                $this->json(400, ['error' => 'Email and password are required']);
                return;
            }

            $result = $this->service->login((string) $payload['email'], (string) $payload['password']);
            $this->json(200, $result);
        } catch (RuntimeException) {
            $this->json(401, ['error' => 'Invalid credentials']);
        } catch (PDOException $e) {
            $this->json(500, [
                'error' => 'Database connection failed',
                'hint' => 'Проверьте PostgreSQL и файл .env. Выполните скрипты из папки DB/.',
            ]);
        }
    }

    private function json(int $code, array $payload): void
    {
        http_response_code($code);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
