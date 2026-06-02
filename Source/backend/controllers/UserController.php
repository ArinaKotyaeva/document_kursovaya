<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/AuthContext.php';
require_once __DIR__ . '/../services/UserService.php';

class UserController
{
    private UserService $service;

    public function __construct()
    {
        $this->service = UserService::getInstance();
    }

    public function index(): void
    {
        $this->json(200, $this->service->getAll());
    }

    public function show(int $id): void
    {
        $user = $this->service->findById($id);

        if ($user === null) {
            $this->json(404, ['error' => 'User not found']);
            return;
        }

        $this->json(200, $user->toArray());
    }

    public function store(array $payload): void
    {
        $userId = AuthContext::id() ?? 0;
        $user = $this->service->create(
            trim($payload['name']),
            trim($payload['email']),
            isset($payload['age']) && $payload['age'] !== '' ? (int) $payload['age'] : null,
            $userId
        );
        $this->json(201, $user->toArray());
    }

    public function update(int $id, array $payload): void
    {
        $user = $this->service->update($id, $payload);

        if ($user === null) {
            $this->json(404, ['error' => 'User not found']);
            return;
        }

        $this->json(200, $user->toArray());
    }

    public function destroy(int $id): void
    {
        $userId = AuthContext::id() ?? 0;

        if (!$this->service->delete($id, $userId)) {
            $this->json(404, ['error' => 'User not found']);
            return;
        }

        $this->json(200, ['success' => true, 'id' => $id]);
    }

    private function json(int $code, array $payload): void
    {
        http_response_code($code);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
