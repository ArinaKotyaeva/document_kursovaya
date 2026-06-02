<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/UserService.php';

class UserController
{
  private UserService $service; #свойство

  public function __construct()
  {
    $this->service = UserService::getInstance();
  }

  public function index(): void
  {
    $this->jsonResponse(200, $this->service->getAll());
  }

  public function show(int $id): void
  {
    $user = $this->service->findById($id);

    if ($user === null) {
      $this->jsonResponse(404, ['error' => 'User not found']);
      return;
    }

    $this->jsonResponse(200, $user->toArray());
  }

  public function store(array $payload): void
  {
    $name = trim($payload['name']);
    $email = trim($payload['email']);
    $age = null;

    if (array_key_exists('age', $payload) && $payload['age'] !== null && $payload['age'] !== '') {
      $age = (int) $payload['age'];
    }

    $user = $this->service->create($name, $email, $age);
    $this->jsonResponse(201, $user->toArray());
  }

  public function destroy(int $id): void
  {
    if (!$this->service->delete($id)) {
      $this->jsonResponse(404, ['error' => 'User not found']);
      return;
    }

    $this->jsonResponse(200, ['success' => true, 'id' => $id]);
  }

  private function jsonResponse(int $code, array $payload): void
  {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  }
}
