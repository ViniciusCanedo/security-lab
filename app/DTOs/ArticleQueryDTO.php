<?php

namespace App\DTOs;

use App\Enums\ArticleStatus;

readonly class ArticleQueryDTO
{
    public function __construct(
        public ?ArticleStatus $status = null,
        public ?int $authorId = null,
        public ?string $search = null,
        public int $perPage = 15,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = null;
        if (! empty($data['status'])) {
            $status = is_string($data['status']) ? ArticleStatus::tryFrom($data['status']) : $data['status'];
        }

        return new self(
            status: $status,
            authorId: isset($data['author_id']) ? (int) $data['author_id'] : null,
            search: $data['search'] ?? null,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 15,
        );
    }
}
