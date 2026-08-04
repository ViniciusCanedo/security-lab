<?php

use App\DTOs\CreateCommentDTO;
use App\Enums\ArticleStatus;
use App\Events\CommentPosted;
use App\Listeners\SendCommentNotificationToAuthor;
use App\Mail\NewCommentAuthorNotificationMail;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\CommentRepositoryInterface;
use App\Services\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('dispatches CommentPosted event when comment is created', function () {
    Event::fake([CommentPosted::class]);

    $commentRepo = mock(CommentRepositoryInterface::class);
    $articleRepo = mock(ArticleRepositoryInterface::class);

    $article = new Article(['id' => 10, 'status' => ArticleStatus::PUBLISHED]);

    $comment = new Comment([
        'id' => 1,
        'article_id' => 10,
        'user_id' => 2,
        'content' => 'Test comment',
    ]);
    $comment->article = $article;

    $articleRepo->shouldReceive('findById')->with(10)->andReturn($article);
    $commentRepo->shouldReceive('create')->andReturn($comment);

    $service = new CommentService($commentRepo, $articleRepo);
    $dto = new CreateCommentDTO(2, 10, 'Test comment');

    $service->create($dto);

    Event::assertDispatched(CommentPosted::class, function ($event) use ($comment) {
        return $event->comment === $comment;
    });
});

test('sends email notification to author when another user comments', function () {
    Mail::fake();

    $author = User::factory()->create(['email' => 'author@example.com']);
    $commenter = User::factory()->create(['email' => 'commenter@example.com']);
    $article = Article::factory()->create(['user_id' => $author->id, 'status' => ArticleStatus::PUBLISHED]);

    $comment = new Comment([
        'id' => 10,
        'article_id' => $article->id,
        'user_id' => $commenter->id,
        'content' => 'Great article!',
    ]);
    $comment->setRelation('article', $article);
    $comment->setRelation('user', $commenter);

    $listener = new SendCommentNotificationToAuthor;
    $listener->handle(new CommentPosted($comment));

    Mail::assertSent(NewCommentAuthorNotificationMail::class, function ($mail) use ($author) {
        return $mail->hasTo($author->email);
    });
});

test('does not send email notification when author comments on own article', function () {
    Mail::fake();

    $author = User::factory()->create(['email' => 'author@example.com']);
    $article = Article::factory()->create(['user_id' => $author->id, 'status' => ArticleStatus::PUBLISHED]);

    $comment = new Comment([
        'id' => 11,
        'article_id' => $article->id,
        'user_id' => $author->id,
        'content' => 'Self comment',
    ]);
    $comment->setRelation('article', $article);
    $comment->setRelation('user', $author);

    $listener = new SendCommentNotificationToAuthor;
    $listener->handle(new CommentPosted($comment));

    Mail::assertNothingSent();
});
