<?php

namespace App\Mail;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCommentAuthorNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Comment $comment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo comentário no seu artigo',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new_comment_author',
        );
    }
}
