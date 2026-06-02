<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/AuthContext.php';
require_once __DIR__ . '/../config/Database.php';

class LoggingMiddleware
{
    public static function log(string $method, string $path, int $statusCode, int $startedAt): void
    {
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO api_request_logs (user_id, method, path, status_code, duration_ms)
                 VALUES (:user_id, :method, :path, :status_code, :duration_ms)'
            );
            $stmt->execute([
                'user_id' => AuthContext::id(),
                'method' => $method,
                'path' => $path,
                'status_code' => $statusCode,
                'duration_ms' => max(0, (int) (microtime(true) * 1000) - $startedAt),
            ]);
        } catch (Throwable) {
        }
    }
}
