<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Url;


class UrlHelper
{
    /**
     * @param string $urlPart
     * @param array $urlToSlug
     * @param string $charBetweenFieldAndValues
     * @param string $charBetweenValues
     * @return array
     */
    public static function parseFilterPart(
        string $urlPart,
        array $urlToSlug = [],
        string $charBetweenFieldAndValues = '-',
        string $charBetweenValues = '-or-'
    ): array {
        $result = [];
        $urlPart = trim(trim($urlPart, '/'));

        if(empty($urlPart)) {
            return [];
        }

        $parts = explode('/', $urlPart);

        foreach ($parts as $filterString) {
            preg_match('#^(\w+)' . $charBetweenFieldAndValues . '(.*?)$#', $filterString, $matches);
            $field = $urlToSlug[ $matches[1] ] ?? $matches[1];
            $values = $matches[2];
            $result[ $field ] = explode($charBetweenValues, $values);
        }

        return $result;
    }

    /**
     * @param array $fieldsWithValue
     * @param array $slugToUrl =[]
     * @param string $charBetweenFieldAndValues
     * @param string $charBetweenValues
     * @return string
     */
    public function buildFilterPart(
        array $fieldsWithValue,
        array $slugToUrl = [],
        string $charBetweenFieldAndValues = '-',
        string $charBetweenValues = '-or-   '
    ): string {
        $results = [];

        if (!empty($this->afterField)) {
            $results[] = $this->afterField;
        }

        foreach ($fieldsWithValue as $field => $values) {
            $field = $slugToUrl[ $field ] ?? $field;
            sort($values);
            $valuesPart = implode($charBetweenValues, $values);
            $results[] = $field . $charBetweenFieldAndValues . $valuesPart;
        }

        return implode('/', $results);
    }
}
