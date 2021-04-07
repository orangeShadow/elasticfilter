<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use OrangeShadow\ElasticFilter\Contracts\IViewType;
use OrangeShadow\ElasticFilter\Exceptions\TypeFilterException;

class ElasticFilter extends Model implements IViewType
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'category',
        'index',
        'slug',
        'title',
        'type',
        'url_slug',
        'sort',
        'unit',
        'hint'
    ];

    /**
     * @param string $type
     * @return $this
     * @throws TypeFilterException
     */
    public function setTypeAttribute(string $type): self
    {
        $ref = new \ReflectionClass(IViewType::class);
        $typeList = $ref->getConstants();

        if (in_array($type, $typeList, true)) {
            throw new TypeFilterException();
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getUrlSlug(): ?string
    {
        return $this->url_slug;
    }

    /**
     * @param Builder $query
     * @param array $filter
     */
    public function scopeSearch(Builder $query, array $filter)
    {
        $query->when(isset($filter['index']), function (Builder $query) use ($filter) {
            return $query->where('index', $filter['index']);
        });

        $query->when(isset($filter['category']), function (Builder $query) use ($filter) {
            return $query->where('category', $filter['category']);
        });

        $query->when(isset($filter['slug']), function (Builder $query) use ($filter) {
            return $query->where('slug', $filter['slug']);
        });

        $query->when(isset($filter['url_slug']), function (Builder $query) use ($filter) {
            return $query->where('url_slug', $filter['url_slug']);
        });

        $query->orderBy('sort', 'asc');

        return $query;
    }

}
