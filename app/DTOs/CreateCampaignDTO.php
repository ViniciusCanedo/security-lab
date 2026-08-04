<?php

namespace App\DTOs;

readonly class CreateCampaignDTO
{
    public function __construct(
        public int $createdBy,
        public string $title,
        public string $subject,
        public string $content,
        public ?int $articleId = null,
    ) {}

    public static function fromArray(array $data, int $createdBy): self
    {
        return new self(
            createdBy: $createdBy,
            title: $data['title'],
            subject: $data['subject'],
            content: $data['content'],
            articleId: isset($data['article_id']) ? (int) $data['article_id'] : null,
        );
    }
}
