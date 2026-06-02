<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

class UserService
{
    private static ?UserService $instance = null;

    public static function getInstance(): UserService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getAll(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, name, email, age FROM registry_records WHERE is_deleted = FALSE ORDER BY id'
        );

        return array_map(static fn(array $row) => self::mapRow($row)->toArray(), $stmt->fetchAll());
    }

    public function findById(int $id): ?User
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, name, email, age FROM registry_records WHERE id = :id AND is_deleted = FALSE'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? self::mapRow($row) : null;
    }

    public function create(string $name, string $email, ?int $age, int $userId): User
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO registry_records (name, email, age, created_by)
             VALUES (:name, :email, :age, :created_by)
             RETURNING id, name, email, age'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'age' => $age,
            'created_by' => $userId,
        ]);

        return self::mapRow($stmt->fetch());
    }

    public function update(int $id, array $payload): ?User
    {
        $current = $this->findById($id);
        if ($current === null) {
            return null;
        }

        $name = isset($payload['name']) ? trim((string) $payload['name']) : $current->name;
        $email = isset($payload['email']) ? trim((string) $payload['email']) : $current->email;
        $age = array_key_exists('age', $payload)
            ? ($payload['age'] === null || $payload['age'] === '' ? null : (int) $payload['age'])
            : $current->age;

        $stmt = Database::connection()->prepare(
            'UPDATE registry_records SET name = :name, email = :email, age = :age
             WHERE id = :id AND is_deleted = FALSE
             RETURNING id, name, email, age'
        );
        $stmt->execute(['id' => $id, 'name' => $name, 'email' => $email, 'age' => $age]);
        $row = $stmt->fetch();

        return $row ? self::mapRow($row) : null;
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = Database::connection()->prepare('CALL sp_registry_soft_delete(:id, :user_id)');
        try {
            $stmt->execute(['id' => $id, 'user_id' => $userId]);
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    private static function mapRow(array $row): User
    {
        return new User(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['email'],
            $row['age'] !== null ? (int) $row['age'] : null
        );
    }
}
