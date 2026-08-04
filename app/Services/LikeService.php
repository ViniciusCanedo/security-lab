<?php

namespace App\Services;

use App\Enums\ArticleStatus;
use App\Exceptions\ArticleNotPublishedException;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\LikeRepositoryInterface;
use Illuminate\Support\Facades\DB;

class LikeService
{
    public function __construct(
        protected LikeRepositoryInterface $likeRepository,
        protected ArticleRepositoryInterface $articleRepository,
    ) {}

    /**
     * @return array{liked: bool, count: int}
     *
     * @throws ArticleNotPublishedException
     */
    public function toggle(int $userId, int $articleId): array
    {
        $article = $this->articleRepository->findById($articleId);

        if (! $article || $article->status !== ArticleStatus::PUBLISHED) {
            throw new ArticleNotPublishedException;
        }

        return DB::transaction(function () use ($userId, $articleId) {
            $like = $this->likeRepository->findByUserAndArticle($userId, $articleId);

            if ($like) {
                $this->likeRepository->delete($like);
                $liked = false;
            } else {
                $this->likeRepository->create($userId, $articleId);
                $liked = true;
            }

            $count = $this->likeRepository->countByArticle($articleId);

            return [
                'liked' => $liked,
                'count' => $count,
            ];
        });
    }
}
