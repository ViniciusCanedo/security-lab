<?php

namespace App\Repositories\Contracts;

use App\DTOs\ArticleQueryDTO;
use App\DTOs\CreateArticleDTO;
use App\DTOs\UpdateArticleDTO;
use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface
{
    public function findById(int $id): ?Article;

    public function findBySlug(string $slug): ?Article;

    public function findPublishedById(int $id): ?Article;

    public function findPublishedBySlug(string $slug): ?Article;

    public function getPaginated(ArticleQueryDTO $query): LengthAwarePaginator;

    public function getPublishedPaginated(ArticleQueryDTO $query): LengthAwarePaginator;

    public function create(CreateArticleDTO $dto): Article;

    public function update(Article $article, UpdateArticleDTO $dto): Article;

    public function updateStatus(Article $article, string $status): Article;

    public function delete(Article $article): bool;
}
