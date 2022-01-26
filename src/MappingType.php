<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter;


use OrangeShadow\ElasticFilter\Exceptions\MappingTypeNotFoundException;

/**
 * Class MappingType
 * Class for mapping config you can use default type or set yours in
 * @package OrangeShadow\ElasticFilter
 */
class MappingType
{
    public const BYTE = [
        'type' => 'byte'
    ];

    public const BOOL = [
        'type' => 'boolean'
    ];

    public const FILTERED_NESTED = [
        'type'       => 'nested',
        'properties' => [
            'value'    => [
                'type' => 'keyword',
                'normalizer' => 'keyword_lowercase'
            ],
            'slug'     => [
                'type'       => 'keyword',
                'normalizer' => 'keyword_lowercase'
            ],
            'computed' => [
                'type'       => 'keyword'
            ]
        ]
    ];

    public const FLOAT = [
        'type' => 'float'
    ];

    public const KEYWORD = [
        'type' => 'keyword',
    ];

    public const KEYWORD_LOWERCASE = [
        'type'       => 'keyword',
        'normalizer' => 'keyword_lowercase'
    ];

    public const FULL_TEXT = [
        "type"                  => "text",
        "analyzer"              => "all_text",
        "search_analyzer"       => "all_text",
        "search_quote_analyzer" => "all_text"
    ];

    public const INT = [
        'type' => 'integer'
    ];

    public const NO_INDEX = [
        'enabled' => false
    ];

    public const SHORT = [
        'type' => 'short'
    ];

    public const DATE = [
        'type' => 'datetime'
    ];

    /**
     * @param string $type
     * @return array
     * @throws MappingTypeNotFoundException
     */
    public static function getTypeFromConfig(string $type): array
    {
        $mappingTypes = config('elastic_filter.php.mapping_types', []);
        if (!isset($mappingTypes[ $type ])) {
            throw new MappingTypeNotFoundException();
        }

        return $mappingTypes[ $type ];
    }

    /**
     * Compare array https://www.php.net/manual/en/language.operators.array.php
     *
     * @param array $type1
     * @param array $type2
     * @return bool
     *
     */
    public function compareType(array $type1, array $type2): bool
    {
        return $type1 === $type2;
    }
}
