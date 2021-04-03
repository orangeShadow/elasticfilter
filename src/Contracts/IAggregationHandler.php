<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Contracts;


interface IAggregationHandler
{
    /**
     * @param array $result
     *
     * @return mixed
     */
    public function resultHandler(array $result):array;
}
