### Install

```composer require orangeshadow/elasitcfilter```

- 1 ```php artisan vendor:publish```

- 2 Fill all fields in files: ```config/elastic_filter.php``` and ```config/indexes/{catalog}.php```

{catalog} - or your different name, You must enter all datamapping in this file

'className' - class must implement OrangeShadow\ElasticFilter\Contracts\IElasticImport;

- 3 Add next code to AppServiceProvider:

```
    $this->app->bind('ElasticManager', function(){
        $config = new IndexConfig('indexes.catalog');
            return new ElasticManager($config);
    });
```

- 4 Run command for indexing your Data php artisan elastic:filter-index {indexes.catalog}

When you set in config mapping

```
[
    'name' => OrangeShadow\ElasticFilter\MappingType::KEYWORD,
    'price' => OrangeShadow\ElasticFilter\MappingType::FLOAT,
    'stores' = [
        'title' => OrangeShadow\ElasticFilter\MappingType::FILTERED_NESTED
    ]
]
```

You must return in your data (IElasticImport) special array

```
[
    'value' => 'Example title',
    'slug'  => 'slug_for_url',
    'computed' => {title}||{value},
]
```

or you can create object:

```
new \OrangeShadow\ElasticFilter\FilterData($value,{$slug})
```

### How to use ElasticManager

```
$queryParmas = [  
    'stores.title' => 'someTitle',
    'price_from' => 20,
];

$searchProperty = new \OrangeShadow\ElasticFilter\SearchProperty($queryParams);
$searchProperty->setPage(1);
$searchProperty->setSize(10);
$searchProperty->setSort(['id' => 'asc']);

ElasticManager::search($searchProperty);  //to find elements 
ElasticManager::count($searchProperty);  //to get element`s count

ElasticManager::aggregation($queryParams,{$url},$filterFields=[])

Where $filterFields you can set manualy in code or you can set $url and then fields will be get from  ElasticFilter  
```

 
