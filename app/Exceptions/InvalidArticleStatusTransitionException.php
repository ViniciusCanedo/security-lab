<?php

namespace App\Exceptions;

use Exception;

class InvalidArticleStatusTransitionException extends Exception
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Transição de status inválida de '{$from}' para '{$to}'.");
    }
}
