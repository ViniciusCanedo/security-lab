<?php

namespace App\Services;

use App\DTOs\CreateCommentDTO;
use App\DTOs\UpdateCommentDTO;
use App\Enums\ArticleStatus;
use App\Exceptions\ArticleNotPublishedException;
use App\Exceptions\MaxCommentDepthExceededException;
use App\Models\Comment;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentService
{
    public function __construct(
        protected CommentRepositoryInterface $commentRepository,
        protected ArticleRepositoryInterface $articleRepository,
    ) {}

    public function findById(int $id): ?Comment
    {
        return $this->commentRepository->findById($id);
    }

    /**
     * @throws ArticleNotPublishedException
     */
    public function getPaginatedByArticle(int $articleId, int $perPage = 15): LengthAwarePaginator
    {
        $article = $this->articleRepository->findById($articleId);

        if (! $article || $article->status !== ArticleStatus::PUBLISHED) {
            throw new ArticleNotPublishedException;
        }

        return $this->commentRepository->getPaginatedByArticle($articleId, $perPage);
    }

    /**
     * @throws ArticleNotPublishedException
     * @throws MaxCommentDepthExceededException
     */
    public function create(CreateCommentDTO $dto): Comment
    {
        $article = $this->articleRepository->findById($dto->articleId);

        if (! $article || $article->status !== ArticleStatus::PUBLISHED) {
            throw new ArticleNotPublishedException;
        }

        if ($dto->parentId !== null) {
            $parent = $this->commentRepository->findById($dto->parentId);

            if (! $parent) {
                throw new ArticleNotPublishedException('Comentário pai não encontrado.');
            }

            if ($parent->parent_id !== null) {
                throw new MaxCommentDepthExceededException;
            }
        }

        $comment = $this->commentRepository->create($dto);

        \App\Events\CommentPosted::dispatch($comment);

        return $comment;
    }

    public function update(Comment $comment, UpdateCommentDTO $dto): Comment
    {
        return $this->commentRepository->update($comment, $dto);
    }

    public function delete(Comment $comment): bool
    {
        return $this->commentRepository->delete($comment);
    }
}
