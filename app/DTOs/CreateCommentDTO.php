<?php

namespace App\DTOs;

use App\Http\Requests\StoreCommentRequest;

readonly class CreateCommentDTO
{
    public function __construct(
        public int $userId,
        public int $articleId,
        public string $content,
        public ?int $parentId = null,
    ) {}

    public static function fromRequest(StoreCommentRequest $request, int $articleId, ?int $parentId = null): self
    {
        /** @var array{content: string} $validated */
        $validated = $request->validated();

        return new self(
            userId: (int) $request->user()?->id,
            articleId: $articleId,
            content: $validated['content'],
            parentId: $parentId,
        );
    }
}
