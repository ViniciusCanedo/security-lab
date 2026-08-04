<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUser = $request->user();
        // If request user is not set (e.g., during registration/OAuth response before token auth header),
        // we check if $this->resource is the target object.
        $canSeeEmail = ! $authUser || $authUser->id === $this->id || $authUser->hasRole('ADMIN');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($canSeeEmail, $this->email),
            'google_id' => $this->when($canSeeEmail, $this->google_id),
            'avatar' => $this->avatar,
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
