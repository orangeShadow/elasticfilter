<?php
declare(strict_types=1);

namespace OrangeShadow\ElasticFilter\Builder;

use OrangeShadow\ElasticFilter\Contracts\IElasticQueryBuilder;
use OrangeShadow\ElasticFilter\IndexConfig;
use OrangeShadow\ElasticFilter\Repositories\ElasticFilterRepository;

/**
 * Class AbstractAggregationBuilder
 * Build query for aggregation
 *
 * @package OrangeShadow\ElasticFilter\Builder
 */
abstract class AbstractAggregationBuilder implements IElasticQueryBuilder
{
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
}
