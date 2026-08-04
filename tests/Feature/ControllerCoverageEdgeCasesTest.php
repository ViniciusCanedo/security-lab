<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
});

test('article controller endpoints 404 responses for non-existent articles', function () {
    $publisher = User::factory()->create();
    $publisher->assignRole('PUBLISHER');
    Sanctum::actingAs($publisher);

    $this->getJson('/api/v1/articles/manage/99999')->assertNotFound();
    $this->putJson('/api/v1/articles/99999', ['title' => 'Novo'])->assertNotFound();
    $this->patchJson('/api/v1/articles/99999/archive')->assertNotFound();

    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    Sanctum::actingAs($admin);

    $this->deleteJson('/api/v1/articles/99999')->assertNotFound();
});

test('comment controller 404 responses for non-existent comments or draft articles', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/comments/99999/replies', ['content' => 'Resposta'])->assertNotFound();
    $this->putJson('/api/v1/comments/99999', ['content' => 'Novo texto'])->assertNotFound();
    $this->deleteJson('/api/v1/comments/99999')->assertNotFound();

    $draftArticle = Article::factory()->create(['status' => ArticleStatus::DRAFT]);
    $this->getJson("/api/v1/articles/{$draftArticle->id}/comments")->assertStatus(422);
});
