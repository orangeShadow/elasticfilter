<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Exceptions;


use Throwable;

class TypeFilterException extends ElasticFilterException
{
    public function __construct($message = "Filter type is wrong!", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
