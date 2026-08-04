<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can search published articles using scout endpoint', function () {
    $author = User::factory()->create();

    $article1 = Article::factory()->create([
        'user_id' => $author->id,
        'title' => 'Laravel Framework Guide',
        'content' => 'Deep dive into Laravel ecosystem.',
        'status' => ArticleStatus::PUBLISHED,
    ]);

    $article2 = Article::factory()->create([
        'user_id' => $author->id,
        'title' => 'Vue.js Basics',
        'content' => 'Frontend development.',
        'status' => ArticleStatus::PUBLISHED,
    ]);

    $response = $this->getJson('/api/v1/articles/search?q=Laravel');

    $response->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});
