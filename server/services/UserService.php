<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';

class UserService
{
    private static ?UserService $instance = null;

    private array $users = [];

    private int $nextId = 1;

    private string $storageFile;

    private function __construct()
    {
        $this->storageFile = __DIR__ . '/../storage/users.json';
        $this->loadFromStorage();
    }

    public static function getInstance(): UserService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getAll(): array
    {
        return array_map(static fn(User $user) => $user->toArray(), $this->users);
    }

    public function findById(int $id): ?User
    {
        foreach ($this->users as $user) {
            if ($user->id === $id) {
                return $user;
            }
        }

        return null;
    }

    public function create(string $name, string $email, ?int $age = null): User
    {
        $this->syncNextId();
        $id = $this->nextId;
        $user = new User($id, $name, $email, $age);
        $this->users[] = $user;
        $this->nextId = $id + 1;
        $this->saveToStorage();

        return $user;
    }

    public function delete(int $id): bool
    {
        $found = false;
        $this->users = array_values(array_filter(
            $this->users,
            static function (User $user) use ($id, &$found): bool {
                if ($user->id === $id) {
                    $found = true;
                    return false;
                }

                return true;
            }
        ));

        if ($found) {
            $this->syncNextId();
            $this->saveToStorage();
        }

        return $found;
    }

    private function syncNextId(): void
    {
        $this->nextId = $this->resolveNextId();
    }

    private function resolveNextId(): int
    {
        if ($this->users === []) {
            return 1;
        }

        $maxId = 0;
        foreach ($this->users as $user) {
            if ($user->id > $maxId) {
                $maxId = $user->id;
            }
        }

        return $maxId + 1;
    }

    private function loadFromStorage(): void
    {
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!is_file($this->storageFile)) {
            return;
        }

        $raw = file_get_contents($this->storageFile);
        if ($raw === false || $raw === '') {
            return;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return;
        }

        $rows = $data['users'] ?? [];

        if (!is_array($rows)) {
            return;
        }

        $this->users = [];
        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['id'], $row['name'], $row['email'])) {
                continue;
            }

            $age = array_key_exists('age', $row) && $row['age'] !== null
                ? (int) $row['age']
                : null;

            $this->users[] = new User((int) $row['id'], (string) $row['name'], (string) $row['email'], $age);
        }

        $this->syncNextId();
    }

    private function saveToStorage(): void
    {
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $payload = [
            'nextId' => $this->nextId,
            'users' => $this->getAll(),
        ];

        file_put_contents(
            $this->storageFile,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }
}
