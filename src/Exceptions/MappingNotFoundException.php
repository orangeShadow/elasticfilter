<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Exceptions;


use Throwable;

class MappingNotFoundException extends ElasticFilterException
{
    public function __construct($message = "Mapping not found!", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
