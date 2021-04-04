<?php
declare(strict_types=1);

namespace OrangeShadow\ElasticFilter\Builder;

use ONGR\ElasticsearchDSL\Aggregation\AbstractAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\FilterAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\NestedAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\MaxAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\MinAggregation;
use ONGR\ElasticsearchDSL\Search;
use OrangeShadow\ElasticFilter\MappingType;

class AggregationBuilder extends AbstractAggregationBuilder
{
    /**
     * @param array $queryParams
     * @param array $filterFields
     * @return array
     */
    public function build(array $queryParams,  array $filterFields): array
    {
        $search = new Search();

        foreach ($filterFields as $fieldKey) {

            $aggs = $this->getAggs($fieldKey);

            $filterAggs = new FilterAggregation($fieldKey, $this->searchBuilder->getBoolQuery($queryParams));

            foreach ($aggs as $agg) {
                $filterAggs->addAggregation($agg);
            }

            $search->addAggregation($filterAggs);
        }

        return [
            "aggs" => [
                "all_products" => array_merge(["global" => new \stdClass], $search->toArray())
            ]
        ];
    }

    /**
     * @param string $fieldKey
     * @return array
     */
    protected function getAggs(string $fieldKey): array
    {
        if (in_array($fieldKey, $this->getConfig()->getNestedFields(), true)) {
            $mapping = $this->getConfig()->getMapping();
            $keys = explode('.', $fieldKey);
            $firstKey = array_shift($keys);

            if (!isset($mapping[ $firstKey ])) {
                return [];
            }

            return [
                $fieldKey => $this->getNestedAggregation($fieldKey, $firstKey, $mapping[ $firstKey ], $keys)
            ];
        }

        $mapping = $this->getConfig()->getFields();

        if (!isset($mapping[ $fieldKey ])) {
            return [];
        }

        return $this->prepareAggregateType($fieldKey, $mapping[ $fieldKey ]);
    }

    /**
     * @param string|null $key
     * @param array $type
     * @return array
     */
    protected function prepareAggregateType(string $key, array $type): array
    {
        switch ($type) {
            case MappingType::INT:
            case MappingType::FLOAT:
                $minAggs = new MinAggregation($key . "_" . self::RANGE_BOTTOM_NAME);
                $minAggs->setField($key);
                $maxAggs = new MaxAggregation($key . "_" . self::RANGE_TOP_NAME);
                $maxAggs->setField($key);

                return [
                    $key . "_" . self::RANGE_BOTTOM_NAME => $minAggs,
                    $key . "_" . self::RANGE_TOP_NAME    => $maxAggs
                ];
            case MappingType::FILTERED_NESTED:
                $aggs = new NestedAggregation($key, $key);
                $terms = new TermsAggregation($key, $key . '.computed');
                $terms->addParameter('size', 1000);
                $aggs->addAggregation($terms);

                return [$key => $aggs];
            default:
                $terms = new TermsAggregation($key, $key);
                $terms->addParameter('size', 1000);

                return [$key => $terms];
        }
    }

    /**
     * @param string $fullKey
     * @param string $currentKey
     * @param array $type
     * @param array $keys
     * @return AbstractAggregation
     */
    protected function getNestedAggregation(string $fullKey, string $currentKey, array $type, array $keys): AbstractAggregation
    {
        if ($type === MappingType::FILTERED_NESTED) {
            return $this->prepareAggregateType($currentKey, $type)[ $currentKey ];
        }
        $aggs = new NestedAggregation($fullKey, $currentKey);
        $key = array_shift($keys);
        $currentKey = $this->getConfig()->createNestedString($currentKey, $key);
        $type = $type['properties'][ $key ];
        $aggs->addAggregation($this->getNestedAggregation($fullKey, $currentKey, $type, $keys));

        return $aggs;
    }
}
