<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/UserService.php';

class ReportService
{
    public function export(int $userId): array
    {
        $records = UserService::getInstance()->getAll();
        $pdo = Database::connection();
        $stmt = $pdo->prepare('CALL sp_report_register_export(:user_id, :format, :count)');
        $stmt->execute([
            'user_id' => $userId,
            'format' => 'json',
            'count' => count($records),
        ]);

        return [
            'title' => 'Документ учёта — реестр персональных данных',
            'exportedAt' => date('c'),
            'total' => count($records),
            'records' => $records,
        ];
    }
}
