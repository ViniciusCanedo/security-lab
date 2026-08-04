<?php

namespace App\DTOs;

readonly class AdminCreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $role = null,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string, role?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role: $data['role'] ?? null,
        );
    }
}
