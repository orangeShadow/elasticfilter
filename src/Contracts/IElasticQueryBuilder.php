<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Contracts;


interface IElasticQueryBuilder
{
    public function build(array $queryParams): array;
}
