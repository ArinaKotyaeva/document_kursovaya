<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$lines = [];
$lines[] = 'PHP: ' . PHP_VERSION;
$lines[] = 'pdo_pgsql: ' . (extension_loaded('pdo_pgsql') ? 'включён' : 'НЕ НАЙДЕН — включите в php.ini Apache');
$lines[] = 'pgsql: ' . (extension_loaded('pgsql') ? 'включён' : 'выключен');

$envPath = dirname(__DIR__) . '/.env';
$lines[] = '.env: ' . (is_file($envPath) ? $envPath : 'НЕ НАЙДЕН');

if (is_file($envPath)) {
    require_once dirname(__DIR__) . '/bootstrap.php';
    $config = require dirname(__DIR__) . '/config/config.php';
    $db = $config['db'];
    $lines[] = "БД: {$db['user']}@{$db['host']}:{$db['port']}/{$db['name']}";
}

try {
    if (!extension_loaded('pdo_pgsql')) {
        throw new RuntimeException('Расширение pdo_pgsql не загружено в PHP Apache. Откройте C:\\xampp\\php\\php.ini, раскомментируйте extension=pdo_pgsql и extension=pgsql, перезапустите Apache.');
    }

    require_once dirname(__DIR__) . '/bootstrap.php';
    require_once dirname(__DIR__) . '/config/Database.php';
    $pdo = Database::connection();
    $lines[] = 'Подключение: OK';

    $tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename")->fetchAll(PDO::FETCH_COLUMN);
    $lines[] = 'Таблицы: ' . (count($tables) ? implode(', ', $tables) : 'НЕТ — выполните DB/01_schema.sql в pgAdmin');

    if (in_array('app_users', $tables, true)) {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM app_users')->fetchColumn();
        $lines[] = "Записей app_users: {$count}";
    }
} catch (Throwable $e) {
    $lines[] = 'ОШИБКА: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Диагностика</title>
<style>body{font-family:Segoe UI,sans-serif;max-width:720px;margin:32px auto} .err{color:#b91c1c} .ok{color:#0f766e} li{margin:10px 0}</style>
</head>
<body>
<h1>Диагностика PHP + PostgreSQL</h1>
<ul>
<?php foreach ($lines as $line): ?>
  <li class="<?= str_contains($line, 'ОШИБКА') || str_contains($line, 'НЕ') ? 'err' : 'ok' ?>"><?= htmlspecialchars($line) ?></li>
<?php endforeach; ?>
</ul>
<p><a href="install.php">Повторить установку пользователей</a></p>
</body>
</html>
