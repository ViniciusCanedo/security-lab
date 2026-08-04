<?php

use App\Enums\ArticleStatus;
use App\Jobs\IncrementArticleViewCountJob;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('viewing an article dispatches IncrementArticleViewCountJob and returns reading_time_minutes', function () {
    Queue::fake();

    $author = User::factory()->create();
    $article = Article::factory()->create([
        'user_id' => $author->id,
        'title' => 'Article Analytics Test',
        'content' => str_repeat('word ', 400), // ~400 words = 2 minutes reading time
        'status' => ArticleStatus::PUBLISHED,
        'views_count' => 5,
    ]);

    $response = $this->getJson("/api/v1/articles/{$article->id}");

    $response->assertOk()
        ->assertJsonPath('data.views_count', 5)
        ->assertJsonPath('data.reading_time_minutes', 2);

    Queue::assertPushed(IncrementArticleViewCountJob::class, function ($job) use ($article) {
        return $job->articleId === $article->id;
    });
});

test('IncrementArticleViewCountJob increments article views count in database', function () {
    $author = User::factory()->create();
    $article = Article::factory()->create([
        'user_id' => $author->id,
        'status' => ArticleStatus::PUBLISHED,
        'views_count' => 10,
    ]);

    $job = new IncrementArticleViewCountJob($article->id);
    $job->handle();

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'views_count' => 11,
    ]);
});
