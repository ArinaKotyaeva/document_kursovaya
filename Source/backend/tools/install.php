<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$messages = [];

if (!extension_loaded('pdo_pgsql')) {
    $messages[] = 'КРИТИЧНО: в PHP Apache не включён pdo_pgsql. Откройте C:\\xampp\\php\\php.ini → раскомментируйте extension=pdo_pgsql и extension=pgsql → перезапустите Apache.';
} else {
    $messages[] = 'pdo_pgsql в PHP: OK';
}

$envPath = dirname(__DIR__) . '/.env';
if (!is_file($envPath)) {
    $messages[] = 'Файл .env не найден: ' . $envPath;
    $messages[] = 'Скопируйте .env.example в .env и укажите пароль PostgreSQL.';
} else {
    require_once __DIR__ . '/../bootstrap.php';
    $config = require __DIR__ . '/../config/config.php';
    $db = $config['db'];
    $messages[] = "Подключение к: {$db['user']}@{$db['host']}:{$db['port']}/{$db['name']}";

    try {
        require_once __DIR__ . '/../config/Database.php';
        $pdo = Database::connection();
        $messages[] = 'Подключение к PostgreSQL: OK';

        $hasRoles = (bool) $pdo->query("SELECT to_regclass('public.roles')")->fetchColumn();
        if (!$hasRoles) {
            $messages[] = 'Таблицы не найдены. Выполните скрипты DB/01_schema.sql … 05_triggers.sql в pgAdmin (см. DB/SETUP_WINDOWS.md).';
        } else {
            $pdo->exec("INSERT INTO roles (code, name) VALUES ('admin', 'Администратор'), ('guest', 'Гость') ON CONFLICT (code) DO NOTHING");

            $pdo->exec("INSERT INTO app_users (email, password_hash, full_name, role_id)
                SELECT 'admin@document.local', crypt('admin123', gen_salt('bf')), 'Администратор системы', r.id
                FROM roles r WHERE r.code = 'admin'
                ON CONFLICT (email) DO UPDATE SET password_hash = EXCLUDED.password_hash, is_active = TRUE");

            $pdo->exec("INSERT INTO app_users (email, password_hash, full_name, role_id)
                SELECT 'guest@document.local', crypt('guest123', gen_salt('bf')), 'Гость системы', r.id
                FROM roles r WHERE r.code = 'guest'
                ON CONFLICT (email) DO UPDATE SET password_hash = EXCLUDED.password_hash, is_active = TRUE");

            $count = (int) $pdo->query('SELECT COUNT(*) FROM app_users')->fetchColumn();
            $messages[] = "Пользователей в app_users: {$count}";
            $messages[] = 'Вход: admin@document.local / admin123 или guest@document.local / guest123';
        }
    } catch (Throwable $e) {
        $messages[] = 'Ошибка подключения: ' . $e->getMessage();
        $messages[] = 'Чаще всего: PostgreSQL не запущен, неверный пароль в .env, база document_kursovaya не создана.';
        $messages[] = 'Инструкция: DB/SETUP_WINDOWS.md';
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Установка — Документ</title>
  <style>
    body{font-family:Segoe UI,sans-serif;max-width:720px;margin:40px auto;padding:0 16px}
    li{margin:10px 0;line-height:1.5}
    .bad{color:#b91c1c}
    a{color:#2563eb}
  </style>
</head>
<body>
  <h1>Установка тестовых пользователей</h1>
  <ul>
    <?php foreach ($messages as $message): ?>
      <li class="<?= str_contains($message, 'Ошибка') || str_contains($message, 'КРИТИЧНО') || str_contains($message, 'не найден') ? 'bad' : '' ?>">
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
      </li>
    <?php endforeach; ?>
  </ul>
  <p><a href="diagnose.php">Полная диагностика</a></p>
</body>
</html>
