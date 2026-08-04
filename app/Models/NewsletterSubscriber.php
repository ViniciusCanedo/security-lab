<?php

namespace App\Models;

use App\Enums\SubscriberStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $email
 * @property SubscriberStatus $status
 * @property string|null $confirmation_token
 * @property string|null $unsubscribe_token
 * @property Carbon|null $subscribed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['email', 'status', 'confirmation_token', 'unsubscribe_token', 'subscribed_at'])]
class NewsletterSubscriber extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => SubscriberStatus::class,
            'subscribed_at' => 'datetime',
        ];
    }

    public function sends(): HasMany
    {
        return $this->hasMany(NewsletterSend::class, 'subscriber_id');
    }
}
