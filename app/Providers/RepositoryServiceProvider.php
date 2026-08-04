<?php

namespace App\Providers;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\CommentRepositoryInterface;
use App\Repositories\Contracts\LikeRepositoryInterface;
use App\Repositories\Contracts\NewsletterRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ArticleRepositoryEloquent;
use App\Repositories\Eloquent\CommentRepositoryEloquent;
use App\Repositories\Eloquent\LikeRepositoryEloquent;
use App\Repositories\Eloquent\NewsletterRepository;
use App\Repositories\Eloquent\UserRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepositoryEloquent::class
        );

        $this->app->bind(
            ArticleRepositoryInterface::class,
            ArticleRepositoryEloquent::class
        );

        $this->app->bind(
            LikeRepositoryInterface::class,
            LikeRepositoryEloquent::class
        );

        $this->app->bind(
            CommentRepositoryInterface::class,
            CommentRepositoryEloquent::class
        );

        $this->app->bind(
            NewsletterRepositoryInterface::class,
            NewsletterRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
