<?php
declare(strict_types=1);

namespace OrangeShadow\ElasticFilter\Builder;

use OrangeShadow\ElasticFilter\Contracts\IAggregationHandler;
use OrangeShadow\ElasticFilter\IndexConfig;
use OrangeShadow\ElasticFilter\Repositories\ElasticFilterRepository;

/**
 * Class AbstractAggregationBuilder
 * Build query for aggregation
 *
 * @package OrangeShadow\ElasticFilter\Builder
 */
abstract class AbstractAggregationBuilder implements IAggregationHandler
{
    public const RANGE_BOTTOM_NAME = "from";
    public const RANGE_TOP_NAME = "to";

    /**
     * @var IndexConfig
     */
    protected $config;

    /**
     * @var AbstractSearchBuilder
     */
    protected $searchBuilder;

    /**
     * @var ElasticFilterRepository $elasticFilterRepository ;
     */
    protected $elasticFilterRepository;

    /**
     * @param AbstractSearchBuilder $builder
     * @return $this
     */
    public function setSearchBuilder(AbstractSearchBuilder $builder): self
    {
        $this->searchBuilder = $builder;

        return $this;
    }

    /**
     * @return AbstractSearchBuilder
     */
    public function getSearchBuilder(): AbstractSearchBuilder
    {
        return $this->searchBuilder;
    }

    /**
     * AbstractAggregationBuilder constructor.
     * @param IndexConfig $config
     * @param AbstractSearchBuilder $searchBuilder
     */
    public function __construct(IndexConfig $config, AbstractSearchBuilder $searchBuilder)
    {
        $this->config = $config;
        $this->searchBuilder = $searchBuilder;
        $this->elasticFilterRepository = new ElasticFilterRepository();
    }


    /**
     * @return IndexConfig
     */
    public function getConfig(): IndexConfig
    {
        return $this->config;
    }

    /**
     * @param array $queryParams
     * @param array $filterFields
     *
     * @return array
     */
    abstract public function build(array $queryParams, array $filterFields): array;


    /**
     * @param array $result
     * @return array
     */
    public function resultHandler(array $result): array
    {
        $data = [];
        $result = $result['aggregations']['all_products'];;
        foreach ($result as $key => $item) {
            $res = $this->nestedResultWatch($key, $item);

            if (empty($res)) {
                continue;
            }
            $data[ $key ] = $res;
        }

        return $data;
    }

    /**
     * @param $key
     * @param $item
     * @return array
     */
    protected function nestedResultWatch($key, $item): array
    {
        if (isset($item[ $key ])) {
            return $this->nestedResultWatch($key, $item[ $key ]);
        }

        if (isset($item['buckets'])) {
            return $item['buckets'];
        }

        if (isset($item["value"])) {
            return $item;
        }

        if (isset($item[ $key . '_' . self::RANGE_BOTTOM_NAME ],$item[ $key . "_" . self::RANGE_TOP_NAME ])) {
            return [
                $key . '_' . self::RANGE_BOTTOM_NAME => $item[ $key . '_' . self::RANGE_BOTTOM_NAME ],
                $key . "_" . self::RANGE_TOP_NAME => $item[ $key . '_' . self::RANGE_TOP_NAME ],
            ];
        }


        return [];
    }
}
