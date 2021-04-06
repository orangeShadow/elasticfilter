<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Url;


class UrlHandler
{
    protected $charBetweenFieldAndValues = "-";
    protected $charBetweenValues = "-or-";
    protected $afterField = 'filter';
    protected $hasSection = '';
    protected $prefixField = '';


    /**
     * @param string $charBetweenFieldAndValues
     */
    public function setCharBetweenFieldAndValues(string $charBetweenFieldAndValues): self
    {
        $this->charBetweenFieldAndValues = $charBetweenFieldAndValues;

        return $this;
    }

    /**
     * @param string $charBetweenValues
     */
    public function setCharBetweenValues(string $charBetweenValues): self
    {
        $this->charBetweenValues = $charBetweenValues;

        return $this;
    }

    /**
     * @param string $afterField
     */
    public function setAfterField(string $afterField): self
    {
        $this->afterField = $afterField;

        return $this;
    }

    /**
     * @param string $prefixField
     */
    public function setPrefixField(string $prefixField): self
    {
        $this->prefixField = $prefixField;

        return $this;
    }


    /**
     * @param string $url
     * @return UrlParserResult
     */
    public function parse(string $url): UrlParserResult
    {
        $result = new UrlParserResult();

        preg_match('#^(.*?/)?' . $this->afterField . '/(.*?)(\?.*?)?$#', $url, $matches);

        if (!empty($matches[1])) {
            $result->setPrefix($matches[1]);
        }

        if (!empty($matches[2])) {
            $result->setQueryParams($this->parseFilterPart($matches[2], $fieldToNestedMapping));
        }

        return $result;
    }

    /**
     * @param string $urlPart
     * @param array $fieldToNestedMapping
     * @return array
     */
    protected function parseFilterPart(string $urlPart, array $fieldToNestedMapping = []): array
    {
        $result = [];
        $urlPart = trim($urlPart, '/');
        $parts = explode('/', $urlPart);

        foreach ($parts as $filterString) {
            try {
                preg_match('#^(\w+)' . $this->charBetweenFieldAndValues . '(.*?)$#', $filterString, $matches);
                $field = $matches[1];

                if (isset($fieldToNestedMapping[ $field ])) {
                    $field = $fieldToNestedMapping[ $field ];
                }

                $values = $matches[2];
                $result[ $field ] = explode($this->charBetweenValues, $values);
            } catch (\Exception $e) {
                dump($filterString, explode($this->charBetweenFieldAndValues, $filterString), $e->getMessage());
                continue;
            }
        }

        return $result;
    }

    /**
     * @param array $fieldsWithValue
     * @param array $nestedFieldMapping =[] [nestedField => slug]
     * @return string
     */
    public function build(array $fieldsWithValue, $nestedFieldMapping = []): string
    {
        $results = [];

        if (!empty($this->afterField)) {
            $results[] = $this->afterField;
        }

        foreach ($fieldsWithValue as $field => $values) {

            if (isset($nestedFieldMapping[ $field ])) {
                $field = $nestedFieldMapping[ $field ];
            }
            sort($values);
            $valuesPart = implode($this->charBetweenValues, $values);
            $results[] = $field . $this->charBetweenFieldAndValues . $valuesPart;
        }

        return implode('/', $results);
    }
}
