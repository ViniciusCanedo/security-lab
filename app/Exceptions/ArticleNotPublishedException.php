<?php

namespace App\Exceptions;

use Exception;

class ArticleNotPublishedException extends Exception
{
    protected $message = 'Apenas artigos publicados permitem esta ação.';
}
