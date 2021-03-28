<?php
return [
    'name'        => 'catalog',
    'mapping'     => [
        'id'    => \OrangeShadow\ElasticFilter\MappingType::INT,
        'title' => \OrangeShadow\ElasticFilter\MappingType::KEYWORD,
    ],
    'settings'    => [],
    'primary_key' => 'id',
    'className'   => 'Example'
];
