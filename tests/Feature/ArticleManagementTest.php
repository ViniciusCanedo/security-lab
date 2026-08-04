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

test('public endpoint returns only published articles', function () {
    Article::factory()->published()->count(3)->create();
    Article::factory()->count(2)->create(['status' => ArticleStatus::DRAFT]);
    Article::factory()->archived()->count(2)->create();

    $response = $this->getJson('/api/v1/articles');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('public endpoint can retrieve a single published article by id or slug', function () {
    $publishedArticle = Article::factory()->published()->create([
        'title' => 'Artigo Exemplo',
        'slug' => 'artigo-exemplo',
    ]);

    $draftArticle = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

    $responseById = $this->getJson("/api/v1/articles/{$publishedArticle->id}");
    $responseById->assertOk()->assertJsonPath('data.slug', 'artigo-exemplo');

    $responseBySlug = $this->getJson("/api/v1/articles/{$publishedArticle->slug}");
    $responseBySlug->assertOk()->assertJsonPath('data.id', $publishedArticle->id);

    $responseDraft = $this->getJson("/api/v1/articles/{$draftArticle->id}");
    $responseDraft->assertNotFound();
});

test('publisher and admin can create article draft', function () {
    $publisher = User::factory()->create();
    $publisher->assignRole('PUBLISHER');
    Sanctum::actingAs($publisher);

    $response = $this->postJson('/api/v1/articles', [
        'title' => 'Meu Novo Artigo',
        'content' => 'Conteúdo detalhado do artigo...',
        'summary' => 'Resumo...',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Meu Novo Artigo')
        ->assertJsonPath('data.status', 'draft');
});

test('common user cannot create article', function () {
    $common = User::factory()->create();
    $common->assignRole('COMMON');
    Sanctum::actingAs($common);

    $response = $this->postJson('/api/v1/articles', [
        'title' => 'Tentativa de Artigo',
        'content' => 'Conteúdo...',
    ]);

    $response->assertForbidden();
});

test('publisher can edit own article but not another author article', function () {
    $publisher1 = User::factory()->create();
    $publisher1->assignRole('PUBLISHER');

    $publisher2 = User::factory()->create();
    $publisher2->assignRole('PUBLISHER');

    $article1 = Article::factory()->create(['user_id' => $publisher1->id]);
    $article2 = Article::factory()->create(['user_id' => $publisher2->id]);

    Sanctum::actingAs($publisher1);

    // Editar próprio artigo
    $responseOwn = $this->putJson("/api/v1/articles/{$article1->id}", [
        'title' => 'Título Atualizado',
    ]);
    $responseOwn->assertOk()->assertJsonPath('data.title', 'Título Atualizado');

    // Editar artigo de outro autor
    $responseOther = $this->putJson("/api/v1/articles/{$article2->id}", [
        'title' => 'Tentativa de Atualização',
    ]);
    $responseOther->assertForbidden();
});

test('admin can edit any article', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');

    $publisher = User::factory()->create();
    $publisher->assignRole('PUBLISHER');
    $article = Article::factory()->create(['user_id' => $publisher->id]);

    Sanctum::actingAs($admin);

    $response = $this->putJson("/api/v1/articles/{$article->id}", [
        'title' => 'Atualizado pelo Admin',
    ]);

    $response->assertOk()->assertJsonPath('data.title', 'Atualizado pelo Admin');
});

test('status transition rules draft -> published -> archived are enforced', function () {
    $publisher = User::factory()->create();
    $publisher->assignRole('PUBLISHER');
    Sanctum::actingAs($publisher);

    $article = Article::factory()->create([
        'user_id' => $publisher->id,
        'status' => ArticleStatus::DRAFT,
    ]);

    // Transição draft -> published
    $responsePublish = $this->putJson("/api/v1/articles/{$article->id}", [
        'status' => 'published',
    ]);
    $responsePublish->assertOk()->assertJsonPath('data.status', 'published');

    // Transição published -> archived (via endpoint archive)
    $responseArchive = $this->patchJson("/api/v1/articles/{$article->id}/archive");
    $responseArchive->assertOk()->assertJsonPath('data.status', 'archived');

    // Tentativa de voltar archived -> published deve falhar em Service
    $responseInvalid = $this->putJson("/api/v1/articles/{$article->id}", [
        'status' => 'published',
    ]);
    $responseInvalid->assertStatus(500); // Exception de domínio capturada no pipeline
});

test('only admin can delete an article', function () {
    $publisher = User::factory()->create();
    $publisher->assignRole('PUBLISHER');
    $article = Article::factory()->create(['user_id' => $publisher->id]);

    // Tentativa por publisher
    Sanctum::actingAs($publisher);
    $resPub = $this->deleteJson("/api/v1/articles/{$article->id}");
    $resPub->assertForbidden();

    // Deleção por admin
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    Sanctum::actingAs($admin);

    $resAdmin = $this->deleteJson("/api/v1/articles/{$article->id}");
    $resAdmin->assertNoContent();

    $this->assertSoftDeleted('articles', ['id' => $article->id]);
});
