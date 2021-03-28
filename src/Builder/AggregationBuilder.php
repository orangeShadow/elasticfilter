<?php
declare(strict_types=1);

namespace OrangeShadow\ElasticFilter\Builder;

use ONGR\ElasticsearchDSL\Aggregation\AbstractAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\FilterAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\NestedAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\ReverseNestedAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\MaxAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\MinAggregation;
use ONGR\ElasticsearchDSL\Search;

class AggregationBuilder extends AbstractAggregationBuilder
{
    /**
     * @param array $queryParams
     * @param string $url ''
     * @return array
     */
//    public function build(array $queryParams, string $url = ''): array
//    {
//        if (!empty($url)) {
//            $this->getElasticFilter($url);
//        }
//
//        $search = new Search();
//        $search->addQuery($this->getBoolQuery($queryParams));
//
//        return $search->toArray();
//    }

    /**
     * @param string $url
     * @return array
     */
    protected function getElasticFilter(string $url): array
    {
        $filterList = $this->elasticFilterRepository->search([
            'url'   => $url,
            'index' => $this->config->getName()
        ])->get();

        $filterFields = [];

        foreach ($filterList as $filter) {
            $filterFields[] = $filter->slug;
        }

        return $filterFields;
    }

    /**
     * @param array $queryParams
     *
     * @return array
     */
    public function build(array $queryParams): array
    {
        $search = new Search();

        /**
         * @var IndexMappingElement $item
         */
        foreach ($this->filterFields as $key => $type) {
            if ($item->getBitrixIblockId() === $this->config->getOfferIBlockId()) {
                $aggs = $this->prepareOfferAggregateType($item, $queryParams);
            } else {
                $aggs = $this->prepareAggregateType($item);
            }


            /**
             * @var AbstractAggregation $agg
             */
            foreach ($aggs as $keyName => $agg) {
                $curQueryParams = $queryParams;

                //Исключаем из фильтрации при группировке фильтр по этому полю
                if (isset($curQueryParams[ $item->getName() ])) {
                    unset($curQueryParams[ $item->getName() ]);
                } else {
                    unset($curQueryParams[ $item->getName() . '_to' ]);
                    unset($curQueryParams[ $item->getName() . '_from' ]);
                }

                if ($item->getBitrixIblockId() === $this->config->getOfferIBlockId()) {
                    unset($curQueryParams[ 'offers_' . $item->getName() ]);
                }

                $filterAggs = new FilterAggregation($keyName, $this->searchBuilder->getBoolQuery($curQueryParams));
                $filterAggs->addAggregation($agg);
                $search->addAggregation($filterAggs);
            }
        }

        return [
            "aggs" => [
                "all_products" => array_merge(["global" => new \stdClass], $search->toArray())
            ]
        ];
    }

    /**
     * @param Mapable $element
     * @param array $curQueryParams
     */
    protected function prepareOfferAggregateType(Mapable $element, array $curQueryParams): array
    {
        unset($curQueryParams[ 'offers_' . $element->getName() ]);
        $offerKey = 'offers.' . $element->getName();

        $aggs = new NestedAggregation($offerKey, 'offers');
        $subAggs = new NestedAggregation($offerKey, $offerKey);

        $hasInFilter = $element->isHasInFilter();
        foreach ($element->getProperties() as $item) {
            if ($hasInFilter && $item->getName()!=='computed') {
                continue;
            }

            $sumKey = $offerKey . "." . $item->getName();
            foreach ($this->prepareAggregateType($item, $sumKey) as $agg) {
                /**
                 * TODO:
                 * Добавляем хак для подсчета кол-ва остатков в магазине
                 */
                if ($element->getName() === 'noone_stores4filter') {
                    $agg->addAggregation(new ReverseNestedAggregation('noone_stores4filter_value'));
                }
                $subAggs->addAggregation($agg);
            }
        }
        $aggs->addAggregation($subAggs);

        return [$offerKey => $aggs];
    }


    /**
     * @param Mapable $element
     * @param string|null $key
     * @return array
     */
    protected function prepareAggregateType(Mapable $element, ?string $key = null): array
    {
        $key = $key ?? $element->getName();

        switch ($element->getType()) {
            case TypeEnum::INT:
            case TypeEnum::FLOAT:
                $minAggs = new MinAggregation($key . "_" . self::RANGE_BOTTOM_NAME);
                $minAggs->setField($key);
                $maxAggs = new MaxAggregation($key . "_" . self::RANGE_TOP_NAME);
                $maxAggs->setField($key);

                return [
                    $key . "_" . self::RANGE_BOTTOM_NAME => $minAggs,
                    $key . "_" . self::RANGE_TOP_NAME    => $maxAggs
                ];
            case TypeEnum::NESTED:
                $aggs = new NestedAggregation($key, $key);
                foreach ($element->getProperties() as $item) {
                    if (in_array($item->getName(), ['title', 'value'], true)) {
                        continue;
                    }
                    $sumKey = $key . "." . $item->getName();
                    foreach ($this->prepareAggregateType($item, $sumKey) as $agg) {
                        $aggs->addAggregation($agg);
                    }
                }

                return [$key => $aggs];
            default:
                $terms = new TermsAggregation($key, $key);
                $terms->addParameter('size', 1000);

                return [$key => $terms];
        }
    }
}
