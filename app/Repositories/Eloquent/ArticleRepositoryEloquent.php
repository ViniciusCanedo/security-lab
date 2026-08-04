<?php

namespace App\Repositories\Eloquent;

use App\DTOs\ArticleQueryDTO;
use App\DTOs\CreateArticleDTO;
use App\DTOs\UpdateArticleDTO;
use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ArticleRepositoryEloquent implements ArticleRepositoryInterface
{
    public function findById(int $id): ?Article
    {
        return Article::with('author')->find($id);
    }

    public function findBySlug(string $slug): ?Article
    {
        return Article::with('author')->where('slug', $slug)->first();
    }

    public function findPublishedById(int $id): ?Article
    {
        return Article::with('author')
            ->published()
            ->where('id', $id)
            ->first();
    }

    public function findPublishedBySlug(string $slug): ?Article
    {
        return Article::with('author')
            ->published()
            ->where('slug', $slug)
            ->first();
    }

    public function getPaginated(ArticleQueryDTO $query): LengthAwarePaginator
    {
        $builder = Article::with('author')->latest();

        if ($query->status !== null) {
            $builder->where('status', $query->status);
        }

        if ($query->authorId !== null) {
            $builder->where('user_id', $query->authorId);
        }

        if ($query->search !== null && trim($query->search) !== '') {
            $search = '%'.trim($query->search).'%';
            $builder->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('summary', 'like', $search)
                    ->orWhere('content', 'like', $search);
            });
        }

        return $builder->paginate($query->perPage);
    }

    public function getPublishedPaginated(ArticleQueryDTO $query): LengthAwarePaginator
    {
        $builder = Article::with('author')
            ->published()
            ->latest();

        if ($query->authorId !== null) {
            $builder->where('user_id', $query->authorId);
        }

        if ($query->search !== null && trim($query->search) !== '') {
            $search = '%'.trim($query->search).'%';
            $builder->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('summary', 'like', $search)
                    ->orWhere('content', 'like', $search);
            });
        }

        return $builder->paginate($query->perPage);
    }

    public function create(CreateArticleDTO $dto): Article
    {
        $slug = Str::slug($dto->title);
        if (Article::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.Str::random(6);
        }

        return Article::create([
            'user_id' => $dto->userId,
            'title' => $dto->title,
            'slug' => $slug,
            'summary' => $dto->summary,
            'content' => $dto->content,
            'cover_image_url' => $dto->coverImageUrl,
            'status' => $dto->status,
        ]);
    }

    public function update(Article $article, UpdateArticleDTO $dto): Article
    {
        $payload = array_filter([
            'title' => $dto->title,
            'summary' => $dto->summary,
            'content' => $dto->content,
            'cover_image_url' => $dto->coverImageUrl,
            'status' => $dto->status,
        ], fn ($val) => $val !== null);

        if ($dto->title !== null && $dto->title !== $article->title) {
            $slug = Str::slug($dto->title);
            if (Article::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
                $slug = $slug.'-'.Str::random(6);
            }
            $payload['slug'] = $slug;
        }

        $article->update($payload);

        return $article->fresh(['author']);
    }

    public function updateStatus(Article $article, string $status): Article
    {
        $article->update(['status' => $status]);

        return $article->fresh(['author']);
    }

    public function delete(Article $article): bool
    {
        return (bool) $article->delete();
    }
}
