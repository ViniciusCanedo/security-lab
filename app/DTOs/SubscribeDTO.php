<?php

namespace App\DTOs;

readonly class SubscribeDTO
{
    public function __construct(
        public string $email
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email']
        );
    }
}
