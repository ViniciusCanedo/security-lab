<?php

namespace App\Repositories\Eloquent;

use App\Models\Like;
use App\Repositories\Contracts\LikeRepositoryInterface;

class LikeRepositoryEloquent implements LikeRepositoryInterface
{
    public function findByUserAndArticle(int $userId, int $articleId): ?Like
    {
        return Like::where('user_id', $userId)
            ->where('article_id', $articleId)
            ->first();
    }

    public function create(int $userId, int $articleId): Like
    {
        return Like::create([
            'user_id' => $userId,
            'article_id' => $articleId,
        ]);
    }

    public function delete(Like $like): bool
    {
        return (bool) $like->delete();
    }

    public function countByArticle(int $articleId): int
    {
        return Like::where('article_id', $articleId)->count();
    }
}
