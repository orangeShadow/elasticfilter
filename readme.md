### About

#### What this package is for?

The package is needed mainly for e-commerce applications or for applications where you need a smart filter (facets).

#### What can a package ?

This package allows you to configure an index (or several indexes) to transfer your data to Elasticsearch.
After that you have an easy way to get aggregations on the specified properties, both first nesting level and deeper, quick filtering on that data. 
The package also has classes to help you parse the url and assemble the url according to the specified properties

For example, we have a nice url:

```/catalog/tv/filter/brand-samsung-or-lg-or-sony```

UrlHelper will help us parse the url, and the ElasticManager class will help us select products from the tv section, filtering by selected brands

Also UrlHeper can do the opposite, and build a link to the page from the passed properties

The same console command will help you load your data into the elastic, but you will have to monitor the updates yourself. (For example, hang events on updates of the desired model)

#### What the package can't do?

The package can't do a full context search, but you can describe the index as you want and extend any class by inheriting from it or by implementing an interface.

But as experience shows, to create a good search you will have to write your own preprocessor for input parsing and additional elasticity settings such as ngram, synonyms, make a combination of phrase or cross fields queries. That's why it's better to create a separate index for searching, which will be filled in separately and return some unique key after the search.

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
### Examples

For example you can use OrangeShadow\ElasticFilter\Models\ElasticFilter model and
OrangeShadow\ElasticFilter\Repositories\ElasticFilterRepository

1. Run migrations 
2. Enter data to ElasticFilter table
3. Add next code to your roting
```
use \OrangeShadow\ElasticFilter as EF;

Route::get('/aggs/{category}/filter/{queryParts}', function (string $category, string $queryParts) {
    // Get all fields for aggregation by category or sub url path  
    $aggsFieldHelper = new EF\Url\AggsFieldHelper($category, EF\ElasticManager::getConfig());
    
    //ParseQuery by url it is for seo, you can use without wthis if you want
    //ex:queryParts = color-beloe-or-rozovoe/country-avstraliya  
    $queryParams = EF\Url\UrlHelper::parseFilterPart($queryParts, $aggsFieldHelper->getUrlToSlug());
    
    //Get aggregated fields from elasticsearch
    $res = EF\ElasticManager::aggregation($queryParams, $aggsFieldHelper->getSlugs());
    //exp $res = ['color'=>['White||white', 'Red||red'],strength=>['strength_from'=>3,'strength_to'=>12]]  
})->where(['section' => '.*', 'queryParts' => '.*?']);
```   
