<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter;


use Illuminate\Support\Facades\Facade;

/**
 * Class ElasticManager
 *
 * @method static array search(SearchProperty $searchProperty)
 * @method static int count(SearchProperty $searchProperty)
 *
 */
class ElasticManager extends Facade
{
    protected static function getFacadeAccessor()
    {
       return 'ElasticManager';
    }
}
