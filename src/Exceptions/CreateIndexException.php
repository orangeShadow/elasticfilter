<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Exceptions;


use Throwable;

class CreateIndexException extends ElasticFilterException
{
    public function __construct( Throwable $previous = null)
    {
        $message = "Error on index creation";
        $code = 500;
        parent::__construct($message, $code, $previous);
    }
}
