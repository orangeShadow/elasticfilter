<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Exceptions;


use Throwable;

class IndexNameNotFoundException extends ElasticFilterException
{
    public function __construct($message = "Index name not found!", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
