<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter;


use Illuminate\Support\Facades\Facade;

class ElasticManager extends Facade
{
    protected static function getFacadeAccessor()
    {
       return 'ElasticManager';
    }
}
