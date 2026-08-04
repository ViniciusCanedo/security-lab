<?php

namespace App\Services;

use App\DTOs\ArticleQueryDTO;
use App\DTOs\CreateArticleDTO;
use App\DTOs\UpdateArticleDTO;
use App\Enums\ArticleStatus;
use App\Exceptions\InvalidArticleStatusTransitionException;
use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ArticleService
{
    public function __construct(
        protected ArticleRepositoryInterface $repository
    ) {}

    public function findById(int $id): ?Article
    {
        return $this->repository->findById($id);
    }

    public function findBySlug(string $slug): ?Article
    {
        return $this->repository->findBySlug($slug);
    }

    public function findPublishedById(int $id): ?Article
    {
        return $this->repository->findPublishedById($id);
    }

    public function findPublishedBySlug(string $slug): ?Article
    {
        return $this->repository->findPublishedBySlug($slug);
    }

    public function getPaginated(ArticleQueryDTO $query): LengthAwarePaginator
    {
        return $this->repository->getPaginated($query);
    }

    public function getPublishedPaginated(ArticleQueryDTO $query): LengthAwarePaginator
    {
        return $this->repository->getPublishedPaginated($query);
    }

    public function create(CreateArticleDTO $dto): Article
    {
        return $this->repository->create($dto);
    }

    public function update(Article $article, UpdateArticleDTO $dto): Article
    {
        if ($dto->status !== null && $dto->status !== $article->status) {
            if (! $article->status->canTransitionTo($dto->status)) {
                throw new InvalidArticleStatusTransitionException(
                    $article->status->value,
                    $dto->status->value
                );
            }
        }

        return $this->repository->update($article, $dto);
    }

    public function archive(Article $article): Article
    {
        if (! $article->status->canTransitionTo(ArticleStatus::ARCHIVED)) {
            throw new InvalidArticleStatusTransitionException(
                $article->status->value,
                ArticleStatus::ARCHIVED->value
            );
        }

        return $this->repository->updateStatus($article, ArticleStatus::ARCHIVED->value);
    }

    public function delete(Article $article): bool
    {
        $actor = auth()->user();

        Log::channel('audit')->info('Article deleted', [
            'actor_id' => $actor?->id,
            'article_id' => $article->id,
            'action' => 'article_deletion',
            'ip' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);

        return $this->repository->delete($article);
    }
}
