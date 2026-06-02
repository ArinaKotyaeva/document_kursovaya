<?php

declare(strict_types=1);

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '5432',
        'name' => getenv('DB_NAME') ?: 'document_kursovaya',
        'user' => getenv('DB_USER') ?: 'postgres',
        'password' => getenv('DB_PASSWORD') ?: 'postgres',
    ],
    'jwt_secret' => getenv('JWT_SECRET') ?: 'document_kursovaya_secret_change_me',
    'jwt_ttl' => (int) (getenv('JWT_TTL') ?: 28800),
];
