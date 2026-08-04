<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CreateCommentDTO;
use App\DTOs\UpdateCommentDTO;
use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentRepositoryEloquent implements CommentRepositoryInterface
{
    public function findById(int $id): ?Comment
    {
        return Comment::with(['user', 'replies.user'])->find($id);
    }

    public function getPaginatedByArticle(int $articleId, int $perPage = 15): LengthAwarePaginator
    {
        return Comment::where('article_id', $articleId)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(CreateCommentDTO $dto): Comment
    {
        /** @var Comment $comment */
        $comment = Comment::create([
            'user_id' => $dto->userId,
            'article_id' => $dto->articleId,
            'parent_id' => $dto->parentId,
            'content' => $dto->content,
        ]);

        return $comment->load(['user', 'replies.user']);
    }

    public function update(Comment $comment, UpdateCommentDTO $dto): Comment
    {
        $comment->update([
            'content' => $dto->content,
        ]);

        return $comment->fresh(['user', 'replies.user']) ?? $comment;
    }

    public function delete(Comment $comment): bool
    {
        return (bool) $comment->delete();
    }
}
