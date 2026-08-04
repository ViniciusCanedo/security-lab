<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case DRAFT = 'draft';
    case SENDING = 'sending';
    case COMPLETED = 'completed';
}
