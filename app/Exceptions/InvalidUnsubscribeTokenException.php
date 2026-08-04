<?php

namespace App\Exceptions;

use Exception;

class InvalidUnsubscribeTokenException extends Exception
{
    protected $message = 'Token de cancelamento de inscrição inválido.';
}
