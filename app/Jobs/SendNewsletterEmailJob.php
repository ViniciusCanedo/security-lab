<?php

namespace App\Jobs;

use App\Enums\SendStatus;
use App\Mail\NewsletterCampaignMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Repositories\Contracts\NewsletterRepositoryInterface;
use Illuminate\Bus\Batchable;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNewsletterEmailJob implements ShouldQueue
{
    use Batchable, Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public int $campaignId,
        public int $subscriberId
    ) {
        $this->onQueue('newsletter');
    }

    public function handle(NewsletterRepositoryInterface $repository): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $campaign = NewsletterCampaign::find($this->campaignId);
        $subscriber = NewsletterSubscriber::find($this->subscriberId);

        if (! $campaign || ! $subscriber) {
            return;
        }

        $sendRecord = $repository->createSendRecord($campaign->id, $subscriber->id);

        $unsubscribeUrl = url("/api/v1/newsletter/unsubscribe?token={$subscriber->unsubscribe_token}");

        Mail::to($subscriber->email)->send(
            new NewsletterCampaignMail($campaign, $subscriber, $unsubscribeUrl)
        );

        $repository->updateSendRecord($sendRecord, SendStatus::SENT->value);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error("SendNewsletterEmailJob failed for campaign {$this->campaignId} and subscriber {$this->subscriberId}", [
            'campaign_id' => $this->campaignId,
            'subscriber_id' => $this->subscriberId,
            'exception' => $exception?->getMessage(),
        ]);

        $repository = app(NewsletterRepositoryInterface::class);
        $sendRecord = $repository->createSendRecord($this->campaignId, $this->subscriberId);
        $repository->updateSendRecord($sendRecord, SendStatus::FAILED->value, $exception?->getMessage());
    }
}
