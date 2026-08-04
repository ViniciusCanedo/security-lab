<?php

namespace App\Repositories\Contracts;

use App\Models\Like;

interface LikeRepositoryInterface
{
    public function findByUserAndArticle(int $userId, int $articleId): ?Like;

    public function create(int $userId, int $articleId): Like;

    public function delete(Like $like): bool;

    public function countByArticle(int $articleId): int;
}
