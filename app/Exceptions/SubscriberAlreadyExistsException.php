<?php

namespace App\Exceptions;

use Exception;

class SubscriberAlreadyExistsException extends Exception
{
    protected $message = 'Este e-mail já está cadastrado na newsletter.';
}
