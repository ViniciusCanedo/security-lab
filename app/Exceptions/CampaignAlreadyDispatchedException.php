<?php

namespace App\Exceptions;

use Exception;

class CampaignAlreadyDispatchedException extends Exception
{
    protected $message = 'Esta campanha já foi enviada ou está em processo de envio.';
}
