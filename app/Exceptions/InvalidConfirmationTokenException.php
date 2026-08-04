<?php

namespace App\Exceptions;

use Exception;

class InvalidConfirmationTokenException extends Exception
{
    protected $message = 'Token de confirmação inválido ou expirado.';
}
