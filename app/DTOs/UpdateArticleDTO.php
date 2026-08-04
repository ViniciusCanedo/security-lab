<?php

namespace App\DTOs;

use App\Enums\ArticleStatus;

readonly class UpdateArticleDTO
{
    public function __construct(
        public ?string $title = null,
        public ?string $content = null,
        public ?string $summary = null,
        public ?string $coverImageUrl = null,
        public ?ArticleStatus $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = null;
        if (array_key_exists('status', $data) && $data['status'] !== null) {
            $status = is_string($data['status']) ? ArticleStatus::from($data['status']) : $data['status'];
        }

        return new self(
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            summary: array_key_exists('summary', $data) ? $data['summary'] : null,
            coverImageUrl: array_key_exists('cover_image_url', $data) ? $data['cover_image_url'] : null,
            status: $status,
        );
    }
}
