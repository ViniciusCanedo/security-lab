<?php

namespace App\Http\Resources;

use App\Models\NewsletterCampaign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NewsletterCampaign
 */
class NewsletterCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subject' => $this->subject,
            'content' => $this->content,
            'article_id' => $this->article_id,
            'status' => $this->status->value,
            'created_by' => $this->created_by,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
