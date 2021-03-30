<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Managers;


use Elasticsearch\ClientBuilder;
use OrangeShadow\ElasticFilter\Builder\AbstractAggregationBuilder;
use OrangeShadow\ElasticFilter\Builder\AbstractSearchBuilder;
use OrangeShadow\ElasticFilter\Builder\AggregationBuilder;
use OrangeShadow\ElasticFilter\Builder\SearchBuilder;
use OrangeShadow\ElasticFilter\Contracts\IResourceable;
use OrangeShadow\ElasticFilter\Exceptions\CreateIndexException;
use OrangeShadow\ElasticFilter\IndexConfig;
use OrangeShadow\ElasticFilter\SearchProperty;


class ElasticManager
{
    private const CONFIG_FILE = 'elastic_filter';

    /**
     * @var IndexConfig
     */
    private $config;

    /**
     * @var
     */
    private $client;

    /**
     * @var AbstractSearchBuilder
     */
    private $searchBuilder;

    /**
     * @var AbstractAggregationBuilder
     */
    private $aggregationBuilder;

    /**
     * ElasticManager constructor.
     * @param IndexConfig $config
     * @param null $client
     */
    public function __construct(IndexConfig $config, $client = null)
    {
        $this->config = $config;
        $this->client = $client;

        if (!$client) {
            $elasticIp = \config(self::CONFIG_FILE . '.elastic_ip') ?: env('ELASTIC_IP');
            $elasticUser = \config(self::CONFIG_FILE . '.elastic_name') ?: env('ELASTIC_NAME','');
            $elasticPass = \config(self::CONFIG_FILE . '.elastic_password') ?: env('ELASTIC_PASSWORD','');
            $this->client = ClientBuilder::create()
                ->setHosts([$elasticIp])
                ->setBasicAuthentication($elasticUser, $elasticPass)
                ->build();
        }

        if (\config(self::CONFIG_FILE . '.searchBuilder')) {
            $searchBuilderClassName = \config(self::CONFIG_FILE.'.searchBuilder');
            $this->searchBuilder = new $searchBuilderClassName($config);
        } else {
            $this->searchBuilder = new SearchBuilder($config);
        }

        if (\config(self::CONFIG_FILE . '.aggregationBuilder')) {
            $aggregationBuilderClassName = \config(self::CONFIG_FILE.'.aggregationBuilder');
            $this->aggregationBuilder = new $aggregationBuilderClassName($config,$this->searchBuilder);
        } else {
            $this->aggregationBuilder = new AggregationBuilder($config, $this->searchBuilder);
        }
    }

    /**
     * @return AbstractSearchBuilder
     */
    public function getSearchBuilder(): AbstractSearchBuilder
    {
        return $this->searchBuilder;
    }

    /**
     * @param AbstractSearchBuilder $searchBuilder
     * @return self
     */
    public function setSearchBuilder(AbstractSearchBuilder $searchBuilder): self
    {
        $this->searchBuilder = $searchBuilder;

        return $this;
    }

    /**
     * @return AbstractAggregationBuilder
     */
    public function getAggregationBuilder(): AbstractAggregationBuilder
    {
        return $this->aggregationBuilder;
    }

    /**
     * @param AbstractAggregationBuilder $aggregationBuilder
     * @return self
     */
    public function setAggregationBuilder(AbstractAggregationBuilder $aggregationBuilder): self
    {
        $this->aggregationBuilder = $aggregationBuilder;

        return $this;
    }

    /**
     * @return IndexConfig
     */
    public function getConfig(): IndexConfig
    {
        return $this->config;
    }

    /**
     * @param IndexConfig $config
     * @return self
     */
    public function setConfig(IndexConfig $config): self
    {
        $this->config = $config;

        return $this;
    }


    /**
     * Create Index in Elastic
     * Get name from IndexConfig and add date postfix
     *
     * @return string newIndexName
     *
     * @throws CreateIndexException
     */
    public function createIndex(): ?string
    {
        $indexName = $this->getConfig()->getName();

        $newIndexName = $indexName . '_' . date('Y_m_d_H_i_s');

        try {
            $params = [
                'index' => $newIndexName,
                'body'  => [
                    'settings' => $this->getConfig()->getSettings(),
                    'mappings' => [
                        'properties' => $this->getConfig()->getMapping()
                    ]
                ]
            ];

            $this->client->indices()->create($params);
        } catch (\Throwable $e) {
            $this->deleteIndexByName($newIndexName);
            throw new CreateIndexException($e);
        }

        return $newIndexName;
    }

    /**
     * Delete all indexes with alias and set alias to current fresh index
     *
     * @param string $currentIndexName
     */
    public function setAlias(string $currentIndexName): void
    {
        $alias = $this->getConfig()->getName();
        $this->deleteIndexByName($currentIndexName, $alias);
        $this->addAliasToIndex($currentIndexName, $alias);
    }

    /**
     * @param string $indexName
     * @param string $alias
     */
    protected function addAliasToIndex(string $indexName, string $alias): void
    {
        $params = [];
        $params['body'] = [
            'actions' => [
                [
                    'add' => [
                        'index' => $indexName,
                        'alias' => $alias
                    ]
                ]
            ]
        ];

        $this->client->indices()->updateAliases($params);
    }

    /**
     * @param string|null $exceptIndexName
     * @param string|null $indexName
     *
     * @return bool
     */
    public function deleteIndexByName(string $exceptIndexName = null, string $indexName = null): bool
    {
        try {
            if (is_null($indexName)) {
                $indexName = $this->getConfig()->getName();
            }

            if (is_null($exceptIndexName)) {
                $this->client->indices()->delete(['index' => $indexName]);

                return true;
            }

            $indexes = $this->client->indices()->getAlias(['index' => $indexName . '*']);

            foreach ($indexes as $trueIndexName => $aliases) {
                if ($exceptIndexName === $trueIndexName) {
                    continue;
                }
                $this->client->indices()->delete(['index' => $trueIndexName]);
            }

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Проверка наличия индекса
     * @param string $indexName
     * @return bool
     */
    public function checkIndexExist(string $indexName): bool
    {
        return $this->client->indices()->exists(['index' => $indexName]);
    }

    /**
     * @param string $id
     * @param array $source
     */
    public function addElement(string $id, array $source): void
    {
        $this->client->create([
            'index' => $this->config->getName(),
            'id'    => $id,
            'body'  => $source
        ]);
    }

    /**
     * @param string $id
     * @param array $source
     */
    public function updateElement(string $id, array $source): void
    {
        try {
            $this->deleteElement($id);
        } catch (\Exception $e) {
            //Ничего не делаем, может падать ошибка если нет в индексе
        } finally {
            $this->addElement($id, $source);
        }
    }

    /**
     * Заменяет разницу между тем что в базе и тем, что пришло
     * @param string $id
     * @param array $diff
     */
    public function updateDiffElement(string $id, array $diff): void
    {
        $element = $this->getElement($id);

        foreach ($diff as $key => $item) {
            $element[ $key ] = $item;
        }
        $this->deleteElement($id);

        $this->addElement($id, $element);
    }

    /**
     * @param string $id
     */
    public function deleteElement(string $id): void
    {
        $this->client->delete([
            'index' => $this->config->getName(),
            'id'    => $id
        ]);
    }

    /**
     * @param string $id
     * @return array| null
     */
    public function getElement(string $id): ?array
    {
        $res = $this->client->get([
            'index' => $this->config->getName(),
            'id'    => $id
        ]);

        return $res["_source"] ?? null;
    }

    /**
     * @param SearchProperty $searchProperty
     * @return mixed|null
     */
    public function search(SearchProperty $searchProperty)
    {
        $body = [];

        $body['from'] = $searchProperty->getFrom();
        $body['size'] = $searchProperty->getSize();

        if (!empty($searchProperty->getSort())) {
            $body['sort'] = $searchProperty->getSort();
        }

        $body['_source'] = $searchProperty->getSource();

        $body = array_merge($body, $this->searchBuilder->build($searchProperty->getQueryParams()));
        $result = $this->client->search([
            'index' => $this->config->getName(),
            'body'  => $body
        ]);


        if (empty($result['hits']['hits'])) {
            return null;
        }

        return $result['hits']['hits'];
    }

    /**
     * @param array $queryParams
     * @param string $url
     * @param IResourceable|null $resource
     * @return array|callable
     */
    public function aggregation(array $queryParams, string $url = '', ?IResourceable $resource = null): array
    {
        $body = array_merge(
            ['size' => 0],
            $this->searchBuilder->build($queryParams),
            $this->aggregationBuilder->build($queryParams, $url)
        );

        $results = $this->client->search([
            'index' => $this->getConfig()->getName(),
            'body'  => $body
        ]);

        if (is_null($resource)) {
            return $results;
        }

        return $resource->toArray($results);
    }

    /**
     * Получить кол-во элементов
     *
     * @param SearchProperty $searchProperty
     * @return int
     */
    public function count(SearchProperty $searchProperty): int
    {
        $param = [
            'index' => $this->getConfig()->getName(),
            'body'  => $this->searchBuilder->build($searchProperty->getQueryParams())
        ];

        $result = $this->client->count($param);

        if (empty($result['count'])) {
            return 0;
        }

        return $result['count'];
    }
}
