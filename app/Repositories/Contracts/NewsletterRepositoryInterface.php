<?php

namespace App\Repositories\Contracts;

use App\DTOs\CreateCampaignDTO;
use App\DTOs\SubscribeDTO;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSend;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Collection;

interface NewsletterRepositoryInterface
{
    public function findSubscriberByEmail(string $email): ?NewsletterSubscriber;

    public function findSubscriberByConfirmationToken(string $token): ?NewsletterSubscriber;

    public function findSubscriberByUnsubscribeToken(string $token): ?NewsletterSubscriber;

    public function createSubscriber(SubscribeDTO $dto, string $confirmationToken, string $unsubscribeToken): NewsletterSubscriber;

    public function updateSubscriberStatus(NewsletterSubscriber $subscriber, string $status): bool;

    public function getConfirmedSubscribers(): Collection;

    public function createCampaign(CreateCampaignDTO $dto): NewsletterCampaign;

    public function findCampaignById(int $id): ?NewsletterCampaign;

    public function updateCampaignStatus(NewsletterCampaign $campaign, string $status): bool;

    public function createSendRecord(int $campaignId, int $subscriberId): NewsletterSend;

    public function updateSendRecord(NewsletterSend $send, string $status, ?string $errorMessage = null): bool;

    public function getCampaignSendStats(int $campaignId): array;
}
