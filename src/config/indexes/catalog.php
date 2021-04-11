<?php
use \OrangeShadow\ElasticFilter\MappingType;

return [
    'name'        => 'catalog',
    'mapping'     => [
        'tag' => MappingType::KEYWORD,
        'description' => MappingType::FULL_TEXT,
    ],
    'settings'    => [],
    'primary_key' => 'id',
    'className'   => 'Example'
];
