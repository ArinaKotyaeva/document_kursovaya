<?php

declare(strict_types=1);

class RoleMiddleware
{
  private const ALLOWED_ROLES = ['admin', 'guest'];

  public static function handle(string $method, string $route): ?string
  {
    $role = self::getRoleHeader();

    if ($role === null) {
      self::respond(401, ['error' => 'Unauthorized: missing or invalid X-User-Role header']);
      return null;
    }

    if ($role === 'guest') {
      $guestForbidden = ($method === 'POST' && $route === '/users')
        || ($method === 'DELETE' && $route === '/users/{id}');

      if ($guestForbidden) {
        self::respond(403, ['error' => 'Access denied']);
        return null;
      }
    }

    return $role;
  }

  private static function getRoleHeader(): ?string
  {
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    if (empty($headers)) {
      foreach ($_SERVER as $key => $value) {
        if (str_starts_with($key, 'HTTP_')) {
          $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
          $headers[$name] = $value;
        }
      }
    }

    $role = null;
    foreach ($headers as $name => $value) {
      if (strcasecmp($name, 'X-User-Role') === 0) {
        $role = strtolower(trim((string) $value));
        break;
      }
    }

    if ($role === null || !in_array($role, self::ALLOWED_ROLES, true)) {
      return null;
    }

    return $role;
  }

  private static function respond(int $code, array $payload): void
  {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
  }
}
