<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter;

use Illuminate\Support\Facades\Facade;

/**
 * Class ElasticManager
 *
 * @method static array search(SearchProperty $searchProperty)
 * @method static int count(SearchProperty $searchProperty)
 * @method static array aggregation(array $queryParams,?string $url, array $filterFields=[])
 * @method static IndexConfig getConfig()
 * @method static string|null createIndex() Create Index by config
 * @method static void setAlias(string $currentIndexName) Delete all indexes with alias and set alias to current fresh index
 * @method static void addAliasToIndex(string $indexName, string $alias):
 * @method static bool deleteIndexByName(string $exceptIndexName = null, string $indexName = null)
 * @method static bool checkIndexExist(string $indexName)
 * @method static void addElement(string $id, array $source)
 * @method static void updateElement(string $id, array $source)
 * @method static void deleteElement(string $id):
 * @method static array|null getElement(string $id)
 *
 *
 */
class ElasticManager extends Facade
{
    protected static function getFacadeAccessor()
    {
       return 'ElasticManager';
    }
}
