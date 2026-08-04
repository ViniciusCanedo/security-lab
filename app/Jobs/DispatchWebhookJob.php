<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

class DispatchWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 300];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $subscriptionId,
        public string $event,
        public array $payload
    ) {}

    public function handle(): void
    {
        $subscription = WebhookSubscription::find($this->subscriptionId);

        if (! $subscription || ! $subscription->is_active) {
            return;
        }

        $headers = [
            'Content-Type' => 'application/json',
            'X-Webhook-Event' => $this->event,
        ];

        if ($subscription->secret) {
            $headers['X-Webhook-Signature'] = hash_hmac('sha256', json_encode($this->payload) ?: '', $subscription->secret);
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($subscription->target_url, $this->payload);

            WebhookDelivery::create([
                'webhook_subscription_id' => $subscription->id,
                'event' => $this->event,
                'payload' => $this->payload,
                'status_code' => $response->status(),
                'response_body' => Str($response->body())->limit(1000)->toString(),
                'error_message' => $response->successful() ? null : 'HTTP request returned status '.$response->status(),
                'attempt' => $this->attempts(),
                'delivered_at' => $response->successful() ? now() : null,
            ]);

            if (! $response->successful()) {
                $this->release($this->backoff[min($this->attempts() - 1, count($this->backoff) - 1)]);
            }
        } catch (Throwable $e) {
            WebhookDelivery::create([
                'webhook_subscription_id' => $subscription->id,
                'event' => $this->event,
                'payload' => $this->payload,
                'status_code' => null,
                'response_body' => null,
                'error_message' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'delivered_at' => null,
            ]);

            throw $e;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $subscription = WebhookSubscription::find($this->subscriptionId);
        if ($subscription && $subscription->deliveries()->whereNull('delivered_at')->count() >= 5) {
            $subscription->update(['is_active' => false]);
        }
    }
}
