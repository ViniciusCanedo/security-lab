<?php

namespace App\Enums;

enum SubscriberStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case UNSUBSCRIBED = 'unsubscribed';
}
