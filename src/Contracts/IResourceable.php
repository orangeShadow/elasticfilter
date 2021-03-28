<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Contracts;


interface IResourceable
{
    public function toArray(array $source):array;
}
