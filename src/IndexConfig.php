<?php
declare(strict_types=1);

namespace OrangeShadow\ElasticFilter;

use Illuminate\Support\Arr;
use OrangeShadow\ElasticFilter\Contracts\IElasticImport;
use OrangeShadow\ElasticFilter\Exceptions\IndexConfigException;
use OrangeShadow\ElasticFilter\Exceptions\MappingNotFoundException;

class IndexConfig
{
    private const DEFAULT_SETTINGS = [
        'analysis' => [
            'normalizer' => [
                'keyword_lowercase' => [
                    'type'   => 'custom',
                    'filter' => [
                        'lowercase'
                    ]
                ]
            ]
        ]
    ];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type = "_doc";

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var array
     */
    protected $settings;


    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var string
     */
    protected $className;


    /**
     * All fields type by name with nested path
     * @var array
     */
    protected $fields = [];

    /**
     * Only filtered fields
     * @var array
     */
    protected $filteredFields = [];

    /**
     * Only nested key
     * @var array
     */
    protected $nestedFields = [];

    /**
     * IndexCreator constructor.
     * @param string $indexConfig
     * @throws IndexConfigException
     */
    public function __construct(string $indexConfig)
    {
        $config = config($indexConfig);
        $this->name = Arr::get($config, 'name', 'catalog');
        $this->type = Arr::get($config, 'type', $this->type);
        $this->mapping = Arr::get($config, 'mapping', []);

        $this->prepareMappingByType();

        $settings = array_merge_recursive(Arr::get($config, 'settings', []), self::DEFAULT_SETTINGS);
        $this->settings = $settings;
        $this->className = Arr::get($config, 'className');

        if (empty($this->className)) {
            throw new IndexConfigException('Data class not specified! Please enter "className" in your config file');
        }

        if (!class_exists($this->className)) {
            throw new IndexConfigException("Data class {$this->className} not found!");
        }

        if (!is_a($this->className,IElasticImport::class,true)) {
            throw new IndexConfigException("Class {$this->className} should implement ".IElasticImport::class."!");
        }
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * @param array $mapping
     * @return array
     */
    public function setMapping(array $mapping): self
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    /**
     * @param array $settings
     * @param bool $withDefault = true
     * @return $this
     */
    public function setSettings(array $settings, $withDefault = true): self
    {
        if ($withDefault) {
            $settings = array_merge_recursive($settings, self::DEFAULT_SETTINGS);
        }

        $this->settings = $settings;

        return $this;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey?:'id';
    }

    /**
     * @param string $primaryKey
     */
    public function setPrimaryKey(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get Slug by mapping array
     *
     * @return array
     *
     * @throw MappingNotFoundException
     */
    public function getSlugArray(): array
    {
        $mapping = $this->getMapping();
        if (empty($mapping)) {
            throw new MappingNotFoundException();
        }

        return array_keys($mapping);
    }

    /**
     * @param $parentKey
     * @param $key
     * @param $prevKey
     * @return string
     */
    public function createNestedString($parentKey, $key): string
    {
        return trim(implode('.', [$parentKey, $key]), '.');
    }

    /**
     * Prepare fields for searching and aggregating
     */
    protected function prepareMappingByType() {
        //TODO:May be do this faster, if cached this
        foreach ($this->getMapping() as $key => $item) {
            if ($item === MappingType::FILTERED_NESTED) {
                $this->filteredFields[] = $key;
            }

            if ($item !== MappingType::FILTERED_NESTED && strtolower($item['type']) === 'nested') {
                $this->findNestedKey($key, $item['properties']);
                continue;
            }

            $this->fields[ $key ] = $item;
        }
    }

    /**
     * @param $parentKey
     * @param $properties
     */
    protected function findNestedKey($parentKey, $properties): void
    {
        foreach ($properties as $key => $item) {

            if ($item === MappingType::FILTERED_NESTED) {
                $this->filteredFields[] = $this->createNestedString($parentKey, $key);
            }

            if ($this->checkOnNested($item)) {
                $this->findNestedKey($this->createNestedString($parentKey, $key), $item['properties']);
                continue;
            }
            $this->nestedFields[] = $this->createNestedString($parentKey, $key);
            $this->fields[ $this->createNestedString($parentKey, $key) ] = $item;
        }
    }

    /**
    * @return array
    */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getFilteredFields(): array
    {
        return $this->filteredFields;
    }

    /**
     * @return array
     */
    public function getNestedFields(): array
    {
        return $this->nestedFields;
    }

    /**
     * @param array $item
     **/
    public function checkOnNested(array $item):bool
    {
        return $item !== MappingType::FILTERED_NESTED
            && isset($item['type'])
            && strtolower($item['type']) === 'nested';
    }
}
