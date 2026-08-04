<?php

namespace App\Repositories\Contracts;

use App\DTOs\CreateCommentDTO;
use App\DTOs\UpdateCommentDTO;
use App\Models\Comment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CommentRepositoryInterface
{
    public function findById(int $id): ?Comment;

    public function getPaginatedByArticle(int $articleId, int $perPage = 15): LengthAwarePaginator;

    public function create(CreateCommentDTO $dto): Comment;

    public function update(Comment $comment, UpdateCommentDTO $dto): Comment;

    public function delete(Comment $comment): bool;
}
