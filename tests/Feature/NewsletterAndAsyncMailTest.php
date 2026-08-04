<?php

use App\Enums\CampaignStatus;
use App\Enums\SendStatus;
use App\Enums\SubscriberStatus;
use App\Jobs\SendNewsletterCampaignBatchJob;
use App\Jobs\SendNewsletterEmailJob;
use App\Jobs\SendTransactionalEmailJob;
use App\Mail\ConfirmSubscriptionMail;
use App\Mail\NewsletterCampaignMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSend;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
});

test('visitor can subscribe to newsletter triggering confirmation mail', function () {
    Queue::fake();

    $response = $this->postJson('/api/v1/newsletter/subscribe', [
        'email' => 'leitor@example.com',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'leitor@example.com')
        ->assertJsonPath('data.status', 'pending');

    $this->assertDatabaseHas('newsletter_subscribers', [
        'email' => 'leitor@example.com',
        'status' => 'pending',
    ]);

    Queue::assertPushed(SendTransactionalEmailJob::class, function ($job) {
        return $job->recipientEmail === 'leitor@example.com' && $job->mailable instanceof ConfirmSubscriptionMail;
    });
});

test('subscriber can confirm subscription via confirmation token', function () {
    $subscriber = NewsletterSubscriber::create([
        'email' => 'confirmar@example.com',
        'status' => SubscriberStatus::PENDING,
        'confirmation_token' => 'token-valido-123',
        'unsubscribe_token' => 'unsub-token-123',
    ]);

    $response = $this->getJson('/api/v1/newsletter/confirm?token=token-valido-123');

    $response->assertOk()
        ->assertJsonPath('data.status', 'confirmed');

    $this->assertDatabaseHas('newsletter_subscribers', [
        'id' => $subscriber->id,
        'status' => 'confirmed',
    ]);
});

test('subscriber can unsubscribe via unsubscribe token', function () {
    $subscriber = NewsletterSubscriber::create([
        'email' => 'sair@example.com',
        'status' => SubscriberStatus::CONFIRMED,
        'confirmation_token' => 'conf-123',
        'unsubscribe_token' => 'unsub-456',
        'subscribed_at' => now(),
    ]);

    $response = $this->postJson('/api/v1/newsletter/unsubscribe', [
        'token' => 'unsub-456',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'unsubscribed');

    $this->assertDatabaseHas('newsletter_subscribers', [
        'id' => $subscriber->id,
        'status' => 'unsubscribed',
    ]);
});

test('publisher/admin can create draft newsletter campaign', function () {
    $publisher = User::factory()->create();
    $publisher->assignRole('PUBLISHER');
    Sanctum::actingAs($publisher);

    $response = $this->postJson('/api/v1/admin/newsletter/campaigns', [
        'title' => 'Novidades de Segurança',
        'subject' => 'Confira o nosso novo relatório',
        'content' => '<h1>Conteúdo Completo</h1>',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Novidades de Segurança')
        ->assertJsonPath('data.status', 'draft');
});

test('common user cannot manage newsletter campaigns', function () {
    $common = User::factory()->create();
    $common->assignRole('COMMON');
    Sanctum::actingAs($common);

    $response = $this->postJson('/api/v1/admin/newsletter/campaigns', [
        'title' => 'Sem Permissão',
        'subject' => 'Teste',
        'content' => 'Teste',
    ]);

    $response->assertForbidden();
});

test('publisher/admin can dispatch campaign launching batch jobs', function () {
    Queue::fake();

    $publisher = User::factory()->create();
    $publisher->assignRole('PUBLISHER');
    Sanctum::actingAs($publisher);

    $campaign = NewsletterCampaign::create([
        'title' => 'Campanha 1',
        'subject' => 'Assunto 1',
        'content' => 'Conteúdo 1',
        'created_by' => $publisher->id,
        'status' => CampaignStatus::DRAFT,
    ]);

    $response = $this->postJson("/api/v1/admin/newsletter/campaigns/{$campaign->id}/send");

    $response->assertOk()
        ->assertJsonPath('data.status', 'sending');

    Queue::assertPushed(SendNewsletterCampaignBatchJob::class);
});

test('batch job chunks subscribers and dispatches SendNewsletterEmailJob', function () {
    Bus::fake();

    $publisher = User::factory()->create();
    $publisher->assignRole('PUBLISHER');

    $campaign = NewsletterCampaign::create([
        'title' => 'Campanha Lote',
        'subject' => 'Assunto Lote',
        'content' => 'Conteúdo Lote',
        'created_by' => $publisher->id,
        'status' => CampaignStatus::SENDING,
    ]);

    // Create 3 confirmed subscribers
    NewsletterSubscriber::create(['email' => 'sub1@exp.com', 'status' => SubscriberStatus::CONFIRMED, 'unsubscribe_token' => 't1']);
    NewsletterSubscriber::create(['email' => 'sub2@exp.com', 'status' => SubscriberStatus::CONFIRMED, 'unsubscribe_token' => 't2']);
    NewsletterSubscriber::create(['email' => 'sub3@exp.com', 'status' => SubscriberStatus::UNSUBSCRIBED, 'unsubscribe_token' => 't3']); // Ignored

    $batchJob = new SendNewsletterCampaignBatchJob($campaign->id);
    $batchJob->handle(app(\App\Repositories\Contracts\NewsletterRepositoryInterface::class));

    Bus::assertBatched(function ($batch) {
        return $batch->jobs->count() === 2;
    });
});

test('individual send job executes and updates send status and logs error on failure', function () {
    Mail::fake();

    $publisher = User::factory()->create();
    $campaign = NewsletterCampaign::create([
        'title' => 'Campanha Teste',
        'subject' => 'Assunto',
        'content' => 'Conteúdo',
        'created_by' => $publisher->id,
        'status' => CampaignStatus::SENDING,
    ]);

    $subscriber = NewsletterSubscriber::create([
        'email' => 'recebedor@example.com',
        'status' => SubscriberStatus::CONFIRMED,
        'unsubscribe_token' => 'unsub-token-xyz',
    ]);

    $job = new SendNewsletterEmailJob($campaign->id, $subscriber->id);
    $job->handle(app(\App\Repositories\Contracts\NewsletterRepositoryInterface::class));

    Mail::assertQueued(NewsletterCampaignMail::class, function ($mail) use ($subscriber) {
        return $mail->hasTo($subscriber->email);
    });

    $this->assertDatabaseHas('newsletter_sends', [
        'campaign_id' => $campaign->id,
        'subscriber_id' => $subscriber->id,
        'status' => SendStatus::SENT->value,
    ]);

    // Test failure callback handling
    $job->failed(new Exception('Simulated SMTP Error'));

    $this->assertDatabaseHas('newsletter_sends', [
        'campaign_id' => $campaign->id,
        'subscriber_id' => $subscriber->id,
        'status' => SendStatus::FAILED->value,
        'error_message' => 'Simulated SMTP Error',
    ]);
});

test('admin can view campaign status report', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    Sanctum::actingAs($admin);

    $campaign = NewsletterCampaign::create([
        'title' => 'Relatório Campanha',
        'subject' => 'Assunto',
        'content' => 'Conteúdo',
        'created_by' => $admin->id,
        'status' => CampaignStatus::COMPLETED,
    ]);

    $sub1 = NewsletterSubscriber::create(['email' => 's1@exp.com', 'status' => SubscriberStatus::CONFIRMED]);
    $sub2 = NewsletterSubscriber::create(['email' => 's2@exp.com', 'status' => SubscriberStatus::CONFIRMED]);

    NewsletterSend::create(['campaign_id' => $campaign->id, 'subscriber_id' => $sub1->id, 'status' => SendStatus::SENT]);
    NewsletterSend::create(['campaign_id' => $campaign->id, 'subscriber_id' => $sub2->id, 'status' => SendStatus::FAILED, 'error_message' => 'Error']);

    $response = $this->getJson("/api/v1/admin/newsletter/campaigns/{$campaign->id}/status");

    $response->assertOk()
        ->assertJsonPath('data.total', 2)
        ->assertJsonPath('data.sent', 1)
        ->assertJsonPath('data.failed', 1)
        ->assertJsonPath('data.pending', 0);
});
