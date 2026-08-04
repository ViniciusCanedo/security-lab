<?php

namespace App\DTOs;

readonly class UpdateUserRoleDTO
{
    public function __construct(
        public string $role,
    ) {}

    /**
     * @param  array{role: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            role: $data['role'],
        );
    }
}
