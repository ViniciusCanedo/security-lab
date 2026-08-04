<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTransactionalEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $recipientEmail,
        public Mailable $mailable
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        Mail::to($this->recipientEmail)->send($this->mailable);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error("SendTransactionalEmailJob failed for recipient {$this->recipientEmail}", [
            'recipient' => $this->recipientEmail,
            'mailable' => get_class($this->mailable),
            'exception' => $exception?->getMessage(),
        ]);
    }
}
