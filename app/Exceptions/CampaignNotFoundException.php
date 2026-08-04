<?php

namespace App\Exceptions;

use Exception;

class CampaignNotFoundException extends Exception
{
    protected $message = 'Campanha de newsletter não encontrada.';
}
