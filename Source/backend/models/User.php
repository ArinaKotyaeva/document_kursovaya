<?php

declare(strict_types=1);

class User
{
    public int $id;
    public string $name;
    public string $email;
    public ?int $age;

    public function __construct(int $id, string $name, string $email, ?int $age = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->age = $age;
    }

    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->age !== null) {
            $data['age'] = $this->age;
        }

        return $data;
    }
}
