<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/AuthContext.php';
require_once __DIR__ . '/../services/ReportService.php';

class ReportController
{
    private ReportService $service;

    public function __construct()
    {
        $this->service = new ReportService();
    }

    public function export(): void
    {
        $userId = AuthContext::id() ?? 0;
        $report = $this->service->export($userId);
        http_response_code(200);
        echo json_encode($report, JSON_UNESCAPED_UNICODE);
    }
}
