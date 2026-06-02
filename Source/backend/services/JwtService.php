<?php

declare(strict_types=1);

class JwtService
{
    public static function encode(array $payload): string
    {
        $config = require __DIR__ . '/../config/config.php';
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload['exp'] = time() + $config['jwt_ttl'];

        $segments = [
            self::base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            self::base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR)),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), $config['jwt_secret'], true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $config = require __DIR__ . '/../config/config.php';
        $signature = hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", $config['jwt_secret'], true);
        if (!hash_equals(self::base64UrlEncode($signature), $parts[2])) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($parts[1]), true);
        if (!is_array($payload) || ($payload['exp'] ?? 0) < time()) {
            return null;
        }

        return $payload;
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/'), true) ?: '';
    }
}
