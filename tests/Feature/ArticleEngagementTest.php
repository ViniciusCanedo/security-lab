<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
});

test('authenticated user can toggle like on published article', function () {
    $user = User::factory()->create();
    $user->assignRole('COMMON');
    Sanctum::actingAs($user);

    $article = Article::factory()->published()->create();

    // Primeira curtida: curte o artigo
    $res1 = $this->postJson("/api/v1/articles/{$article->id}/like");
    $res1->assertOk()
        ->assertJsonPath('data.liked', true)
        ->assertJsonPath('data.count', 1);

    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);

    // Segunda curtida: descurte o artigo (toggle idempotente)
    $res2 = $this->postJson("/api/v1/articles/{$article->id}/like");
    $res2->assertOk()
        ->assertJsonPath('data.liked', false)
        ->assertJsonPath('data.count', 0);

    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);
});

test('cannot like draft or archived article', function () {
    $user = User::factory()->create();
    $user->assignRole('COMMON');
    Sanctum::actingAs($user);

    $draftArticle = Article::factory()->create(['status' => ArticleStatus::DRAFT]);
    $archivedArticle = Article::factory()->archived()->create();

    $resDraft = $this->postJson("/api/v1/articles/{$draftArticle->id}/like");
    $resDraft->assertStatus(422);

    $resArchived = $this->postJson("/api/v1/articles/{$archivedArticle->id}/like");
    $resArchived->assertStatus(422);
});

test('authenticated user can comment on published article', function () {
    $user = User::factory()->create();
    $user->assignRole('COMMON');
    Sanctum::actingAs($user);

    $article = Article::factory()->published()->create();

    $res = $this->postJson("/api/v1/articles/{$article->id}/comments", [
        'content' => 'Ótimo artigo sobre segurança!',
    ]);

    $res->assertCreated()
        ->assertJsonPath('data.content', 'Ótimo artigo sobre segurança!')
        ->assertJsonPath('data.article_id', $article->id);

    $this->assertDatabaseHas('comments', [
        'user_id' => $user->id,
        'article_id' => $article->id,
        'content' => 'Ótimo artigo sobre segurança!',
    ]);
});

test('cannot comment on draft or archived article', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $draftArticle = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

    $res = $this->postJson("/api/v1/articles/{$draftArticle->id}/comments", [
        'content' => 'Tentativa de comentário...',
    ]);

    $res->assertStatus(422);
});

test('user can reply to a top level comment up to 1 level depth', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $article = Article::factory()->published()->create();
    $parentComment = Comment::factory()->create([
        'article_id' => $article->id,
        'user_id' => $user->id,
        'content' => 'Comentário principal',
    ]);

    // Responder ao comentário principal (nível 1)
    $resReply = $this->postJson("/api/v1/comments/{$parentComment->id}/replies", [
        'content' => 'Primeira resposta',
    ]);

    $resReply->assertCreated()
        ->assertJsonPath('data.parent_id', $parentComment->id);

    $childCommentId = $resReply->json('data.id');

    // Tentativa de responder à resposta (nível 2 > 1) -> deve ser bloqueado com 422
    $resLevel2 = $this->postJson("/api/v1/comments/{$childCommentId}/replies", [
        'content' => 'Tentativa de resposta nível 2',
    ]);

    $resLevel2->assertStatus(422);
});

test('author can update own comment but not another user comment', function () {
    $author = User::factory()->create();
    $otherUser = User::factory()->create();
    $article = Article::factory()->published()->create();

    $comment = Comment::factory()->create([
        'article_id' => $article->id,
        'user_id' => $author->id,
        'content' => 'Texto original',
    ]);

    // Autor atualizando
    Sanctum::actingAs($author);
    $resOwn = $this->putJson("/api/v1/comments/{$comment->id}", [
        'content' => 'Texto editado',
    ]);
    $resOwn->assertOk()->assertJsonPath('data.content', 'Texto editado');

    // Outro usuário tentando editar
    Sanctum::actingAs($otherUser);
    $resOther = $this->putJson("/api/v1/comments/{$comment->id}", [
        'content' => 'Tentativa maliciosa',
    ]);
    $resOther->assertForbidden();
});

test('author can delete own comment and admin can delete any comment', function () {
    $author = User::factory()->create();
    $otherUser = User::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');

    $article = Article::factory()->published()->create();

    $comment1 = Comment::factory()->create([
        'article_id' => $article->id,
        'user_id' => $author->id,
    ]);
    $comment2 = Comment::factory()->create([
        'article_id' => $article->id,
        'user_id' => $author->id,
    ]);

    // Outro usuário tenta deletar
    Sanctum::actingAs($otherUser);
    $resForbidden = $this->deleteJson("/api/v1/comments/{$comment1->id}");
    $resForbidden->assertForbidden();

    // Próprio autor deleta comment1
    Sanctum::actingAs($author);
    $resAuthorDel = $this->deleteJson("/api/v1/comments/{$comment1->id}");
    $resAuthorDel->assertNoContent();
    $this->assertSoftDeleted('comments', ['id' => $comment1->id]);

    // Admin deleta comment2
    Sanctum::actingAs($admin);
    $resAdminDel = $this->deleteJson("/api/v1/comments/{$comment2->id}");
    $resAdminDel->assertNoContent();
    $this->assertSoftDeleted('comments', ['id' => $comment2->id]);
});
