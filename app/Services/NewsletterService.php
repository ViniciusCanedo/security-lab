<?php

namespace App\Services;

use App\DTOs\CreateCampaignDTO;
use App\DTOs\SubscribeDTO;
use App\Enums\CampaignStatus;
use App\Enums\SubscriberStatus;
use App\Exceptions\CampaignAlreadyDispatchedException;
use App\Exceptions\CampaignNotFoundException;
use App\Exceptions\InvalidConfirmationTokenException;
use App\Exceptions\InvalidUnsubscribeTokenException;
use App\Exceptions\SubscriberAlreadyExistsException;
use App\Jobs\SendNewsletterCampaignBatchJob;
use App\Jobs\SendTransactionalEmailJob;
use App\Mail\ConfirmSubscriptionMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Repositories\Contracts\NewsletterRepositoryInterface;
use Illuminate\Support\Str;

class NewsletterService
{
    public function __construct(
        protected NewsletterRepositoryInterface $repository
    ) {}

    public function subscribe(SubscribeDTO $dto): NewsletterSubscriber
    {
        $existing = $this->repository->findSubscriberByEmail($dto->email);
        if ($existing) {
            if ($existing->status === SubscriberStatus::CONFIRMED) {
                throw new SubscriberAlreadyExistsException;
            }
            // Re-use pending/unsubscribed record by generating a new confirmation token
            $confirmationToken = Str::random(64);
            $existing->update(['confirmation_token' => $confirmationToken, 'status' => SubscriberStatus::PENDING->value]);
            $subscriber = $existing;
        } else {
            $confirmationToken = Str::random(64);
            $unsubscribeToken = Str::random(64);
            $subscriber = $this->repository->createSubscriber($dto, $confirmationToken, $unsubscribeToken);
        }

        $confirmUrl = url("/api/v1/newsletter/confirm?token={$subscriber->confirmation_token}");
        SendTransactionalEmailJob::dispatch(
            $subscriber->email,
            new ConfirmSubscriptionMail($subscriber, $confirmUrl)
        );

        return $subscriber;
    }

    public function confirm(string $token): NewsletterSubscriber
    {
        $subscriber = $this->repository->findSubscriberByConfirmationToken($token);
        if (! $subscriber) {
            throw new InvalidConfirmationTokenException;
        }

        $this->repository->updateSubscriberStatus($subscriber, SubscriberStatus::CONFIRMED->value);

        return $subscriber->fresh();
    }

    public function unsubscribe(string $token): NewsletterSubscriber
    {
        $subscriber = $this->repository->findSubscriberByUnsubscribeToken($token);
        if (! $subscriber) {
            throw new InvalidUnsubscribeTokenException;
        }

        $this->repository->updateSubscriberStatus($subscriber, SubscriberStatus::UNSUBSCRIBED->value);

        return $subscriber->fresh();
    }

    public function createCampaign(CreateCampaignDTO $dto): NewsletterCampaign
    {
        return $this->repository->createCampaign($dto);
    }

    public function dispatchCampaign(int $campaignId): NewsletterCampaign
    {
        $campaign = $this->repository->findCampaignById($campaignId);
        if (! $campaign) {
            throw new CampaignNotFoundException;
        }

        if ($campaign->status !== CampaignStatus::DRAFT) {
            throw new CampaignAlreadyDispatchedException;
        }

        $this->repository->updateCampaignStatus($campaign, CampaignStatus::SENDING->value);

        SendNewsletterCampaignBatchJob::dispatch($campaign->id);

        return $campaign->fresh();
    }

    public function getCampaignStatus(int $campaignId): array
    {
        $campaign = $this->repository->findCampaignById($campaignId);
        if (! $campaign) {
            throw new CampaignNotFoundException;
        }

        $stats = $this->repository->getCampaignSendStats($campaignId);

        return array_merge([
            'campaign_id' => $campaign->id,
            'title' => $campaign->title,
            'status' => $campaign->status->value,
        ], $stats);
    }
}
