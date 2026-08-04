<?php

namespace App\Http\Resources;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Article
 */
class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'content' => $this->content,
            'cover_image_url' => $this->cover_image_url,
            'status' => $this->status->value,
            'author' => new UserResource($this->whenLoaded('author')),
            'likes_count' => $this->likes()->count(),
            'user_has_liked' => $request->user() ? $this->likes()->where('user_id', $request->user()->id)->exists() : false,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
