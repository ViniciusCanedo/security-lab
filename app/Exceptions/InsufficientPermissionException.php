<?php

namespace App\Exceptions;

use Exception;

class InsufficientPermissionException extends Exception
{
    protected $message = 'Você não possui permissão para realizar esta ação.';

    protected $code = 403;
}
