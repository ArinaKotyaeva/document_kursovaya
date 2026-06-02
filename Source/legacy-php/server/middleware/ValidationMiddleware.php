<?php

declare(strict_types=1);

class ValidationMiddleware
{
  public static function validateUserPayload(array $data): ?array
  {
    $errors = [];

    if (!array_key_exists('name', $data) || !is_string($data['name'])) {
      $errors['name'] = 'Name is required and must be a string';
    } else {
      $name = trim($data['name']);
      $length = mb_strlen($name);
      if ($length < 1 || $length > 100) {
        $errors['name'] = 'Name must be between 1 and 100 characters';
      }
    }

    if (!array_key_exists('email', $data) || !is_string($data['email'])) {
      $errors['email'] = 'Email is required and must be a string';
    } elseif (!filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Email format is invalid';
    }

    if (array_key_exists('age', $data) && $data['age'] !== null && $data['age'] !== '') {
      if (filter_var($data['age'], FILTER_VALIDATE_INT) === false || (int) $data['age'] <= 0) {
        $errors['age'] = 'Age must be a positive integer';
      }
    }

    if ($errors !== []) {
      return $errors;
    }

    return null;
  }

  public static function respondValidationError(array $errors): void
  {
    http_response_code(400);
    echo json_encode(['errors' => $errors], JSON_UNESCAPED_UNICODE);
    exit;
  }
}
