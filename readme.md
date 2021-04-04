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
    'computed' => {value}||{slug},
]
```

It's need for printing and filtering data, when value hasn't fit format for url 


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

ElasticManager::aggregation($queryParams,$filterFields) //filterFields - fields for aggregation
```

Where $filterFields you must set manual

For example you can use OrangeShadow\ElasticFilter\Models\ElasticFilter model and
OrangeShadow\ElasticFilter\Repositories\ElasticFilterRepository

1. Run migrations 
2. Enter data to ElasticFilter table

```
    public function __construct() 
    {
        $this->elasticFilterRepository = new ElasticFilterRepository();
        $this->config = new InexConfig('config_file_name');
    }
    
    ...
    
    /**
     * @param string $url
     * @return array
     */
    protected function getFieldsByUrl(string $url): array
    {
        $filterList = $this->elasticFilterRepository->search([
            'uri'   => $uri,
            'index' => $this->config->getName()
        ])->get();

        $filterFields = [];

        foreach ($filterList as $filter) {
            $filterFields[] = $filter->slug;
        }

        return $filterFields;
    }
```

### Url handler object

```
$urlHandler = new UrlHandler();
$res = $urlHandler->parse('/catalog/vino/filter/color-beloe-or-rozovoe/country-avstraliya',['color'=>'offer.color']);

return:
[
    "prefix" => "/catalog/vino",
    "queryParams" => [
        "offer.color" => ["beloe","rozovoe"]  
        "country" => ["avstraliya"]
    ]
]

$res = $urlHandler->build($res['queryParams'],['offer.color'=>'color']));

return: "filter/color-beloe-or-rozovoe/country-avstraliya"
```
 
