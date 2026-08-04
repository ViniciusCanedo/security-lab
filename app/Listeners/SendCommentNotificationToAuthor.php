<?php

namespace App\Listeners;

use App\Events\CommentPosted;
use App\Mail\NewCommentAuthorNotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendCommentNotificationToAuthor implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';

    public function handle(CommentPosted $event): void
    {
        $comment = $event->comment;
        /** @var \App\Models\Article|null $article */
        $article = $comment->article;

        if (! $article || ! $article->author) {
            return;
        }

        // Do not notify if the author commented on their own article
        if ($comment->user_id === $article->user_id) {
            return;
        }

        Mail::to($article->author->email)->send(new NewCommentAuthorNotificationMail($comment));
    }
}
