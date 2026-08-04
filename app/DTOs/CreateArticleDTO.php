<?php

namespace App\DTOs;

use App\Enums\ArticleStatus;

readonly class CreateArticleDTO
{
    public function __construct(
        public int $userId,
        public string $title,
        public string $content,
        public ?string $summary = null,
        public ?string $coverImageUrl = null,
        public ArticleStatus $status = ArticleStatus::DRAFT,
    ) {}

    public static function fromArray(array $data, int $userId): self
    {
        $status = isset($data['status'])
            ? (is_string($data['status']) ? ArticleStatus::from($data['status']) : $data['status'])
            : ArticleStatus::DRAFT;

        return new self(
            userId: $userId,
            title: $data['title'],
            content: $data['content'],
            summary: $data['summary'] ?? null,
            coverImageUrl: $data['cover_image_url'] ?? null,
            status: $status,
        );
    }
}
