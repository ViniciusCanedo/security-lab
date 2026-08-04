<?php

namespace App\Policies;

use App\Models\User;

class NewsletterPolicy
{
    public function manage(User $user): bool
    {
        return $user->can('newsletter.manage') || $user->hasRole('ADMIN') || $user->hasRole('PUBLISHER');
    }
}
