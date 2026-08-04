<?php

namespace App\DTOs;

use App\Http\Requests\UpdateCommentRequest;

readonly class UpdateCommentDTO
{
    public function __construct(
        public string $content,
    ) {}

    public static function fromRequest(UpdateCommentRequest $request): self
    {
        /** @var array{content: string} $validated */
        $validated = $request->validated();

        return new self(
            content: $validated['content'],
        );
    }
}
