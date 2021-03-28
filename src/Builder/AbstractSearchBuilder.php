<?php
declare(strict_types=1);

namespace OrangeShadow\ElasticFilter\Builder;

use OrangeShadow\ElasticFilter\Contracts\IElasticQueryBuilder;
use OrangeShadow\ElasticFilter\IndexConfig;

abstract class AbstractSearchBuilder implements IElasticQueryBuilder
{
    /**
     * @var IndexConfig
     */
    protected $config;

    public function __construct(IndexConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return IndexConfig
     */
    public function getConfig(): IndexConfig
    {
        return $this->config;
    }
}
