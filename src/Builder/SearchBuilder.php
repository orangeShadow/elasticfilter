<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Builder;


use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Query as Query;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Search;
use OrangeShadow\ElasticFilter\IndexConfig;
use OrangeShadow\ElasticFilter\MappingType;


class SearchBuilder extends AbstractSearchBuilder
{

    /**
     * @var IndexConfig
     */
    protected $config;

    /**
     * @var array
     */
    protected $nestedFieldQuery;

    /**
     * @param array $queryParams
     *
     * @return array
     */
    public function build(array $queryParams): array
    {
        $search = new Search();
        $search->addQuery($this->getBoolQuery($queryParams));

        return $search->toArray();
    }

    /**
     * @param array $queryParams
     * @return BuilderInterface
     */
    public function getBoolQuery(array $queryParams): BuilderInterface
    {
        $bool = new BoolQuery();

        foreach ($queryParams as $key => $value) {
            $range = null;

            if ($this->isRange($key)) {
                $range = $this->getBottom($key);
            }

            $boolType = BoolQuery::MUST;

            if ($this->isNegative($key)) {
                $boolType = BoolQuery::MUST_NOT;
            }

            $key = $this->cleanKey($key);

            if (!$this->hasInMapping($key)) {
                continue;
            }

            $type = $this->getFieldType($key);

            if ($this->hasInNestedFields($key)) {
                $this->addToNested($bool, $boolType, $type, $key, $value, $range);
            } else {
                $bool->add(
                    $this->buildQueryByType($type, $key, $value, $range),
                    $boolType
                );
            }
        }

        return $bool;
    }

    /**
     * @param $bool
     * @param string $boolType
     * @param array $type
     * @param string $key
     * @param mixed $value
     * @param string|null $range
     */
    protected function addToNested($bool, string $boolType, array $type, string $key, $value, ?string $range): void
    {
        $keyArr = explode('.', $key);
        $parentKey = array_shift($keyArr);
        $rootType = $this->getTypeFromMapping($parentKey);
        $nested = $this->prepareNestedField($boolType, $rootType, $type, $keyArr, $parentKey, $value, $range);

        if ($nested) {
            $bool->add($nested, $boolType);
        }
    }

    /**
     * @param array $type
     * @param string $key
     * @param mixed $value
     * @param string|null $range
     *
     * @return BuilderInterface|null
     */
    protected function buildQueryByType(array $type, string $key, $value, ?string $range = null): ?BuilderInterface
    {
        switch ($type) {
            case MappingType::FULL_TEXT:
                return new Query\FullText\MatchQuery($key, $value);
            case MappingType::BOOL:
            case MappingType::KEYWORD:
            case MappingType::KEYWORD_LOWERCASE :
                if (is_array($value)) {
                    return new Query\TermLevel\TermsQuery($key, $value);
                }

                return new Query\TermLevel\TermQuery($key, $value);
            case MappingType::INT:
            case MappingType::SHORT:
                if ($range) {
                    return new Query\TermLevel\RangeQuery($key, [$range => (int)$value]);
                }

                return new Query\TermLevel\TermsQuery($key, $value);
            case MappingType::FLOAT:
                if (!empty($range)) {
                    return new Query\TermLevel\RangeQuery($key, [$range => (float)$value]);
                }

                return new Query\TermLevel\TermQuery($key, (float)$value);
            case MappingType::FILTERED_NESTED:
                if (is_array($value)) {
                    return new Query\Joining\NestedQuery($key, new Query\TermLevel\TermsQuery($key . '.slug', $value));
                }

                return new Query\Joining\NestedQuery($key, new Query\TermLevel\TermQuery($key . '.slug', $value));
        }

        return null;
    }

    /**
     * @param string $boolType
     * @param array $rootType
     * @param array $lastType
     * @param array $keyArr
     * @param string $parentKey
     * @param $value
     * @param string|null $range
     * @return BuilderInterface|null
     */
    protected function prepareNestedField(
        string $boolType,
        array $rootType,
        array $lastType,
        array $keyArr,
        string $parentKey,
        $value,
        ?string $range = null
    ): ?BuilderInterface {
        $key = array_shift($keyArr);
        if (empty($key)) {
            $query = $this->buildQueryByType($lastType, $parentKey, $value, $range);
            $keyArr = explode('.', $parentKey);
            array_pop($keyArr);
            $nestedKey = implode('.', $keyArr);
            if (isset($this->nestedFieldQuery[ $nestedKey ])) {
                $this->nestedFieldQuery[ $nestedKey ]->add($query,$boolType);
                return null;
            }

            return $query;
        }

        $rootType = $rootType['properties'][ $key ];
        $nested = $this->prepareNestedField(
            $boolType,
            $rootType,
            $lastType,
            $keyArr,
            $this->getConfig()->createNestedString($parentKey, $key),
            $value,
            $range
        );

        if ($nested === null) {
            return null;
        }

        $bool = new BoolQuery();
        $bool->add($nested, BoolQuery::MUST);
        if (empty($this->nestedFieldQuery[ $parentKey ])) {
            $this->nestedFieldQuery[ $parentKey ] = &$bool;
        }

        return new Query\Joining\NestedQuery($parentKey, $bool);

    }

    /**
     * @param $key
     * @return array
     */
    protected function getFieldType($key): array
    {
        return $this->getConfig()->getFields()[ $key ];
    }

    /**
     * @param $key
     * @return bool
     */
    protected function hasInNestedFields($key): bool
    {
        return in_array($key, $this->getConfig()->getNestedFields(), true);
    }

    /**
     * Check field in elastic mapping
     * @param $key
     * @return bool
     */
    protected function hasInMapping(string $key): bool
    {
        return isset($this->getConfig()->getFields()[ $key ]);
    }

    /**
     * @param string $key
     * @return array
     */
    protected function getTypeFromMapping(string $key): array
    {
        return $this->getConfig()->getMapping()[ $key ];
    }

    /**
     * Check, that key is $range
     * @param string
     * @return bool
     */
    protected function isRange(string $key): bool
    {
        return (bool)preg_match('/_from$|_to$/', $key);
    }

    protected function isNegative(string $key): bool
    {
        return (bool)preg_match('/^\!/', $key);
    }

    /**
     * Get Type of range
     * @param string $key
     * @return string
     */
    protected function getBottom(string $key): string
    {
        if (preg_match('/_from$/', $key)) {
            return 'gte';
        }

        return 'lte';
    }

    /**
     * Remove _frmo|_to from $key
     *
     * @param string $key
     * @return string
     */
    protected function cleanKey(string $key): string
    {
        return preg_replace('/_from$|_to$|^\!/', '', $key);
    }
}
