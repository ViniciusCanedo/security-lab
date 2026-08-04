<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Article $article): bool
    {
        if ($article->status->value === 'published') {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->can('article.edit.any') || $user->hasRole('ADMIN')) {
            return true;
        }

        if ($user->id === $article->user_id && ($user->can('article.edit.own') || $user->hasRole('PUBLISHER'))) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('article.create') || $user->hasRole('PUBLISHER') || $user->hasRole('ADMIN');
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->can('article.edit.any') || $user->hasRole('ADMIN')) {
            return true;
        }

        if ($user->id === $article->user_id && ($user->can('article.edit.own') || $user->hasRole('PUBLISHER'))) {
            return true;
        }

        return false;
    }

    public function archive(User $user, Article $article): bool
    {
        if ($user->can('article.archive') || $user->hasRole('ADMIN')) {
            return true;
        }

        if ($user->id === $article->user_id && ($user->can('article.archive.own') || $user->hasRole('PUBLISHER'))) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->hasRole('ADMIN') || $user->hasPermissionTo('article.delete');
    }
}
