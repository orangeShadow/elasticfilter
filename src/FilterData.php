<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter;


use Illuminate\Support\Str;
use JsonSerializable;

class FilterData implements JsonSerializable
{
    /**
     * @var string
     */
    protected $value;
    /**
     * @var string
     */
    protected $slug;
    /**
     * @var string
     */
    protected $computed;

    /**
     * @var array
     */
    protected $otherData;

    /**
     * FilterData constructor.
     * @param $value
     * @param string|null $slug
     * @param array $otherData
     */
    public function __construct(string $value, string $slug = null, array $otherData = [])
    {
        $this->value = $value;
        if (empty($slug)) {
            $slug = Str::slug($value, '_', 'ru');
        }
        $this->slug = $slug;
        $this->computed = implode('||', [$value, Str::lower($slug)]);
        $this->otherData = $otherData;
    }

    /**
     *
     */
    public function toArray():array
    {
        return array_merge([
            'value'    => $this->value,
            'slug'     => $this->slug,
            'computed' => $this->computed
        ], $this->otherData);
    }

    /**
     * @return array
     */
    public function jsonSerialize():array
    {
        return $this->toArray();
    }
}
