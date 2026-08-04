<?php

namespace App\DTOs;

readonly class UpdateUserPermissionsDTO
{
    /**
     * @param  array<string>  $permissions
     */
    public function __construct(
        public array $permissions,
        public string $action = 'sync', // 'sync', 'grant', 'revoke'
    ) {}

    /**
     * @param  array{permissions: array<string>, action?: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            permissions: $data['permissions'],
            action: $data['action'] ?? 'sync',
        );
    }
}
