<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MagicLinkResetMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $resetUrl
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recuperação de Senha - Security Lab Blog',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "<h1>Olá, {$this->user->name}!</h1><p>Clique no link a seguir para redefinir sua senha: <a href=\"{$this->resetUrl}\">{$this->resetUrl}</a></p><p>Este link expira em 60 minutos.</p>",
        );
    }
}
