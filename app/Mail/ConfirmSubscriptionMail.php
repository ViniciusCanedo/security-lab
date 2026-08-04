<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmSubscriptionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterSubscriber $subscriber,
        public string $confirmUrl
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirme sua inscrição na Newsletter - Security Lab Blog',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "<h1>Confirme sua inscrição</h1><p>Clique no link para confirmar sua inscrição na nossa newsletter: <a href=\"{$this->confirmUrl}\">Confirmar Inscrição</a></p>",
        );
    }
}
