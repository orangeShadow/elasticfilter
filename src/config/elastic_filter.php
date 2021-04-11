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
            ],
            'filter'     => [
                'russian_stop'     => [
                    'type'      => 'stop',
                    'stopwords' => '_russian_'
                ],
                'russian_keywords' => [
                    'type'     => 'keyword_marker',
                    'keywords' => []
                ],
                'russian_stemmer'  => [
                    'type'     => 'stemmer',
                    'language' => 'russian'
                ],
                'english_stemmer'  => [
                    'type'     => 'stemmer',
                    'language' => 'english'
                ],
                'english_stop'     => [
                    'type'      => 'stop',
                    'stopwords' => '_english_'
                ],
//                "synonym"          => [
//                    "type"          => "synonym",
//                    "synonyms_path" => "/usr/share/elasticsearch/config/synonym.txt"
//                ],
                "mynGram"          => [
                    "type"     => "nGram",
                    "min_gram" => 3,
                    "max_gram" => 47
                ]
            ],
            'analyzer'   => [
                'all_text' => [
                    'type'      => 'custom',
                    'tokenizer' => 'standard',
                    'filter'    => [
                        'lowercase',
                        'english_stop',
                        'russian_stop',
                        'english_stemmer',
                        'russian_stemmer',
                        //'synonym',
                        'mynGram'
                    ]
                ]
            ]
        ],
        "max_ngram_diff" => "50",
    ]
];
