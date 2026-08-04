<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Laravel\Scout\Searchable;

/**
 * @property ArticleStatus $status
 */
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'summary',
        'content',
        'cover_image_url',
        'status',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ArticleStatus::PUBLISHED);
    }

    public function getReadingTimeMinutesAttribute(): int
    {
        $words = str_word_count(strip_tags((string) $this->content));

        return (int) max(1, ceil($words / 200));
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => $this->content,
            'status' => $this->status->value,
        ];
    }
}
