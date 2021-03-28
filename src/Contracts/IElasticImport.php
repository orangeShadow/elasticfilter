<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Contracts;


interface IElasticImport
{
    /**
     * @return array
     */
    public static function getDataForElastic(): array;
}
