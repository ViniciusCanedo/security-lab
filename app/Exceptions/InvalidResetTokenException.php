<?php

namespace App\Exceptions;

use Exception;

class InvalidResetTokenException extends Exception
{
    protected $message = 'Token de recuperação de senha inválido ou expirado.';

    protected $code = 422;
}
