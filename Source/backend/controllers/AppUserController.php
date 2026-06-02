<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/AppUserService.php';

class AppUserController
{
    private AppUserService $service;

    public function __construct()
    {
        $this->service = new AppUserService();
    }

    public function index(): void
    {
        $this->json(200, $this->service->getAll());
    }

    public function show(int $id): void
    {
        $user = $this->service->findById($id);

        if ($user === null) {
            $this->json(404, ['error' => 'App user not found']);
            return;
        }

        $this->json(200, $user);
    }

    public function store(array $payload): void
    {
        try {
            $user = $this->service->create($payload);
            $this->json(201, $user);
        } catch (RuntimeException $e) {
            $this->json(400, ['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): void
    {
        $user = $this->service->deactivate($id);

        if ($user === null) {
            $this->json(404, ['error' => 'App user not found']);
            return;
        }

        $this->json(200, $user);
    }

    private function json(int $code, array $payload): void
    {
        http_response_code($code);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
