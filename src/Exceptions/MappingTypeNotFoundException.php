<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Exceptions;


use Throwable;

class MappingTypeNotFoundException extends ElasticFilterException
{
    public function __construct($message = "Mapping type not found", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
