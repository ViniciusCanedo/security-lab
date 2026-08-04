<?php

namespace App\Exceptions;

use Exception;

class MaxCommentDepthExceededException extends Exception
{
    protected $message = 'O nível máximo de aninhamento de respostas é de 1 nível.';
}
