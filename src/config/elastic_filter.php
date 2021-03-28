<?php
return [
    'elastic_ip'         => env('ELASTIC_IP'),
    'elastic_name'       => env('ELASTIC_NAME'),
    'elastic_password'   => env('ELASTIC_PASSWORD'),
    'searchBuilder'      => \OrangeShadow\ElasticFilter\Builder\SearchBuilder::class,
    'aggregationBuilder' => \OrangeShadow\ElasticFilter\Builder\AggregationBuilder::class,
    'mapping_types'      => [],
    'settings'           => [
        'analysis' => [
            'normalizer' => [
                //default normalizer use in default MappingType
                'keyword_lowercase' => [
                    'type'   => 'custom',
                    'filter' => [
                        'lowercase'
                    ]
                ]
            ]
        ]
    ]
];
