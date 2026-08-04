<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CreateCampaignDTO;
use App\DTOs\SubscribeDTO;
use App\Enums\CampaignStatus;
use App\Enums\SendStatus;
use App\Enums\SubscriberStatus;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSend;
use App\Models\NewsletterSubscriber;
use App\Repositories\Contracts\NewsletterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class NewsletterRepository implements NewsletterRepositoryInterface
{
    public function findSubscriberByEmail(string $email): ?NewsletterSubscriber
    {
        return NewsletterSubscriber::where('email', $email)->first();
    }

    public function findSubscriberByConfirmationToken(string $token): ?NewsletterSubscriber
    {
        return NewsletterSubscriber::where('confirmation_token', $token)->first();
    }

    public function findSubscriberByUnsubscribeToken(string $token): ?NewsletterSubscriber
    {
        return NewsletterSubscriber::where('unsubscribe_token', $token)->first();
    }

    public function createSubscriber(SubscribeDTO $dto, string $confirmationToken, string $unsubscribeToken): NewsletterSubscriber
    {
        return NewsletterSubscriber::create([
            'email' => $dto->email,
            'status' => SubscriberStatus::PENDING,
            'confirmation_token' => $confirmationToken,
            'unsubscribe_token' => $unsubscribeToken,
        ]);
    }

    public function updateSubscriberStatus(NewsletterSubscriber $subscriber, string $status): bool
    {
        $updateData = ['status' => $status];
        if ($status === SubscriberStatus::CONFIRMED->value) {
            $updateData['subscribed_at'] = now();
        }

        return $subscriber->update($updateData);
    }

    public function getConfirmedSubscribers(): Collection
    {
        return NewsletterSubscriber::where('status', SubscriberStatus::CONFIRMED)->get();
    }

    public function createCampaign(CreateCampaignDTO $dto): NewsletterCampaign
    {
        return NewsletterCampaign::create([
            'created_by' => $dto->createdBy,
            'title' => $dto->title,
            'subject' => $dto->subject,
            'content' => $dto->content,
            'article_id' => $dto->articleId,
            'status' => CampaignStatus::DRAFT,
        ]);
    }

    public function findCampaignById(int $id): ?NewsletterCampaign
    {
        return NewsletterCampaign::find($id);
    }

    public function updateCampaignStatus(NewsletterCampaign $campaign, string $status): bool
    {
        $updateData = ['status' => $status];
        if ($status === CampaignStatus::COMPLETED->value || $status === CampaignStatus::SENDING->value) {
            if ($status === CampaignStatus::SENDING->value && ! $campaign->sent_at) {
                $updateData['sent_at'] = now();
            }
        }

        return $campaign->update($updateData);
    }

    public function createSendRecord(int $campaignId, int $subscriberId): NewsletterSend
    {
        return NewsletterSend::firstOrCreate(
            ['campaign_id' => $campaignId, 'subscriber_id' => $subscriberId],
            ['status' => SendStatus::PENDING]
        );
    }

    public function updateSendRecord(NewsletterSend $send, string $status, ?string $errorMessage = null): bool
    {
        $updateData = ['status' => $status];
        if ($status === SendStatus::SENT->value) {
            $updateData['sent_at'] = now();
        }
        if ($errorMessage !== null) {
            $updateData['error_message'] = $errorMessage;
        }

        return $send->update($updateData);
    }

    public function getCampaignSendStats(int $campaignId): array
    {
        $sends = NewsletterSend::where('campaign_id', $campaignId)->get();

        return [
            'total' => $sends->count(),
            'sent' => $sends->where('status', SendStatus::SENT)->count(),
            'failed' => $sends->where('status', SendStatus::FAILED)->count(),
            'pending' => $sends->where('status', SendStatus::PENDING)->count(),
        ];
    }
}
