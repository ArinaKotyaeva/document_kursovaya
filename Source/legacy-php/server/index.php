<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-User-Role');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/middleware/RoleMiddleware.php';
require_once __DIR__ . '/middleware/ValidationMiddleware.php';
require_once __DIR__ . '/controllers/UserController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
    $uri = substr($uri, strlen($scriptDir)) ?: '/';
}

$uri = rtrim($uri, '/') ?: '/';

$route = $uri;
$userId = null;

if (preg_match('#^/users/(\d+)$#', $uri, $matches)) {
    $route = '/users/{id}';
    $userId = (int) $matches[1];
}

RoleMiddleware::handle($method, $route);

$controller = new UserController();

if ($method === 'GET' && $route === '/users') {
    $controller->index();
    exit;
}

if ($method === 'GET' && $route === '/users/{id}' && $userId !== null) {
    $controller->show($userId);
    exit;
}

if ($method === 'POST' && $route === '/users') {
    $rawBody = file_get_contents('php://input') ?: '';
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        ValidationMiddleware::respondValidationError(['body' => 'Request body must be valid JSON object']);
    }

    $validationErrors = ValidationMiddleware::validateUserPayload($payload);
    if ($validationErrors !== null) {
        ValidationMiddleware::respondValidationError($validationErrors);
    }

    $controller->store($payload);
    exit;
}

if ($method === 'DELETE' && $route === '/users/{id}' && $userId !== null) {
    $controller->destroy($userId);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Route not found'], JSON_UNESCAPED_UNICODE);
