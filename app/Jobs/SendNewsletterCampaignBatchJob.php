<?php

namespace App\Jobs;

use App\Enums\CampaignStatus;
use App\Models\NewsletterCampaign;
use App\Repositories\Contracts\NewsletterRepositoryInterface;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendNewsletterCampaignBatchJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $campaignId
    ) {
        $this->onQueue('newsletter');
    }

    public function handle(NewsletterRepositoryInterface $repository): void
    {
        $campaign = $repository->findCampaignById($this->campaignId);
        if (! $campaign) {
            return;
        }

        $subscribers = $repository->getConfirmedSubscribers();

        if ($subscribers->isEmpty()) {
            $repository->updateCampaignStatus($campaign, CampaignStatus::COMPLETED->value);
            return;
        }

        $jobs = [];
        /** @var \App\Models\NewsletterSubscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            $repository->createSendRecord($campaign->id, $subscriber->id);
            $jobs[] = new SendNewsletterEmailJob($campaign->id, $subscriber->id);
        }

        $campaignId = $this->campaignId;

        Bus::batch($jobs)
            ->name("Newsletter Campaign #{$campaignId}")
            ->onQueue('newsletter')
            ->then(function (Batch $batch) use ($campaignId) {
                $repo = app(NewsletterRepositoryInterface::class);
                $camp = $repo->findCampaignById($campaignId);
                if ($camp) {
                    $repo->updateCampaignStatus($camp, CampaignStatus::COMPLETED->value);
                }
            })
            ->catch(function (Batch $batch, Throwable $e) use ($campaignId) {
                Log::error("Newsletter Campaign Batch failed for campaign {$campaignId}: " . $e->getMessage());
            })
            ->finally(function (Batch $batch) use ($campaignId) {
                $repo = app(NewsletterRepositoryInterface::class);
                $camp = $repo->findCampaignById($campaignId);
                if ($camp && $camp->status !== CampaignStatus::COMPLETED) {
                    $repo->updateCampaignStatus($camp, CampaignStatus::COMPLETED->value);
                }
            })
            ->dispatch();
    }
}
