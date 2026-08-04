<?php

namespace App\Jobs;

use App\Mail\ResetPasswordMagicLinkEmail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendMagicLinkResetJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public User $user,
        public string $resetUrl
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        Mail::to($this->user->email)->send(new ResetPasswordMagicLinkEmail($this->user, $this->resetUrl));
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SendMagicLinkResetJob failed for user: '.$this->user->id, [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
