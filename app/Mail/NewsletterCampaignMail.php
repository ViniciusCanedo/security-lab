<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterCampaignMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterCampaign $campaign,
        public NewsletterSubscriber $subscriber,
        public string $unsubscribeUrl
    ) {
        $this->onQueue('newsletter');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    public function content(): Content
    {
        $body = $this->campaign->content;
        $footer = "<hr/><p><small>Para cancelar sua inscrição, <a href=\"{$this->unsubscribeUrl}\">clique aqui</a>.</small></p>";

        return new Content(
            htmlString: $body . $footer,
        );
    }
}
