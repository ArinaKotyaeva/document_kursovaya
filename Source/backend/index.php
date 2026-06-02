<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$requestStartedMs = (int) (microtime(true) * 1000);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($requestMethod === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/RoleMiddleware.php';
require_once __DIR__ . '/middleware/ValidationMiddleware.php';
require_once __DIR__ . '/middleware/LoggingMiddleware.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ReportController.php';
require_once __DIR__ . '/controllers/AppUserController.php';

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($requestPath, $scriptDir)) {
    $requestPath = substr($requestPath, strlen($scriptDir)) ?: '/';
}

$uri = rtrim($requestPath, '/') ?: '/';

if ($uri === '/api/docs') {
    header('Content-Type: text/html; charset=utf-8');
    readfile(__DIR__ . '/docs/swagger.html');
    exit;
}

if ($uri === '/openapi.json') {
    readfile(__DIR__ . '/openapi.json');
    exit;
}

$route = $uri;
$entityId = null;

if (preg_match('#^/users/(\d+)$#', $uri, $matches)) {
    $route = '/users/{id}';
    $entityId = (int) $matches[1];
}

if (preg_match('#^/app-users/(\d+)$#', $uri, $matches)) {
    $route = '/app-users/{id}';
    $entityId = (int) $matches[1];
}

try {
    if ($requestMethod === 'POST' && $uri === '/auth/login') {
        $payload = json_decode(file_get_contents('php://input') ?: '', true) ?? [];
        (new AuthController())->login($payload);
        exit;
    }

    AuthMiddleware::handle();
    RoleMiddleware::requireRole($requestMethod, $route);

    if ($requestMethod === 'GET' && $route === '/users') {
        (new UserController())->index();
    } elseif ($requestMethod === 'GET' && $route === '/users/{id}' && $entityId !== null) {
        (new UserController())->show($entityId);
    } elseif ($requestMethod === 'POST' && $route === '/users') {
        RoleMiddleware::requireAdmin();
        $payload = json_decode(file_get_contents('php://input') ?: '', true);
        if (!is_array($payload)) {
            ValidationMiddleware::respondValidationError(['body' => 'Request body must be valid JSON object']);
        }
        $errors = ValidationMiddleware::validateUserPayload($payload);
        if ($errors !== null) {
            ValidationMiddleware::respondValidationError($errors);
        }
        (new UserController())->store($payload);
    } elseif ($requestMethod === 'PATCH' && $route === '/users/{id}' && $entityId !== null) {
        RoleMiddleware::requireAdmin();
        $payload = json_decode(file_get_contents('php://input') ?: '', true) ?? [];
        (new UserController())->update($entityId, $payload);
    } elseif ($requestMethod === 'DELETE' && $route === '/users/{id}' && $entityId !== null) {
        RoleMiddleware::requireAdmin();
        (new UserController())->destroy($entityId);
    } elseif ($requestMethod === 'GET' && $uri === '/reports/export') {
        (new ReportController())->export();
    } elseif ($requestMethod === 'GET' && $uri === '/app-users') {
        RoleMiddleware::requireAdmin();
        (new AppUserController())->index();
    } elseif ($requestMethod === 'GET' && $route === '/app-users/{id}' && $entityId !== null) {
        RoleMiddleware::requireAdmin();
        (new AppUserController())->show($entityId);
    } elseif ($requestMethod === 'POST' && $uri === '/app-users') {
        RoleMiddleware::requireAdmin();
        $payload = json_decode(file_get_contents('php://input') ?: '', true) ?? [];
        (new AppUserController())->store($payload);
    } elseif ($requestMethod === 'DELETE' && $route === '/app-users/{id}' && $entityId !== null) {
        RoleMiddleware::requireAdmin();
        (new AppUserController())->destroy($entityId);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Route not found'], JSON_UNESCAPED_UNICODE);
    }
} finally {
    LoggingMiddleware::log($requestMethod, $uri, http_response_code() ?: 200, $requestStartedMs);
}
