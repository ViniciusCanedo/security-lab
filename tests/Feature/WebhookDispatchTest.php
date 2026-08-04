<?php

use App\Jobs\DispatchWebhookJob;
use App\Models\User;
use App\Models\WebhookSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('dispatches webhook job successfully and logs delivery', function () {
    Http::fake([
        'https://example.com/webhook' => Http::response(['success' => true], 200),
    ]);

    $user = User::factory()->create();
    $subscription = WebhookSubscription::create([
        'user_id' => $user->id,
        'event' => 'article.published',
        'target_url' => 'https://example.com/webhook',
        'secret' => 'supersecret',
        'is_active' => true,
    ]);

    $job = new DispatchWebhookJob($subscription->id, 'article.published', ['article_id' => 123]);
    $job->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.com/webhook' &&
               $request->hasHeader('X-Webhook-Event', 'article.published') &&
               $request->hasHeader('X-Webhook-Signature');
    });

    $this->assertDatabaseHas('webhook_deliveries', [
        'webhook_subscription_id' => $subscription->id,
        'event' => 'article.published',
        'status_code' => 200,
    ]);
});

test('handles webhook failure and records error log', function () {
    Http::fake([
        'https://example.com/webhook-fail' => Http::response('Server Error', 500),
    ]);

    $user = User::factory()->create();
    $subscription = WebhookSubscription::create([
        'user_id' => $user->id,
        'event' => 'article.published',
        'target_url' => 'https://example.com/webhook-fail',
        'is_active' => true,
    ]);

    $job = new DispatchWebhookJob($subscription->id, 'article.published', ['article_id' => 456]);

    try {
        $job->handle();
    } catch (Throwable $e) {
    }

    $this->assertDatabaseHas('webhook_deliveries', [
        'webhook_subscription_id' => $subscription->id,
        'event' => 'article.published',
        'status_code' => 500,
    ]);
});
