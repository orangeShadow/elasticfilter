<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Url;

use OrangeShadow\ElasticFilter\Models\ElasticFilter;
use Ramsey\Collection\Collection;

/**
 * Class SlugHelper
 * @package OrangeShadow\ElasticFilter\Url
 */
class SlugHelper
{
    protected $slugs = [];
    protected $slugToUrl = [];
    protected $urlToSlug = [];

    /**
     * @var \Illuminate\Database\Eloquent\Collection [ElasticFilter]
     */
    protected $collection;

    /**
     * SlugHelper constructor.
     * @param \Illuminate\Database\Eloquent\Collection $collection
     */
    public function __construct(\Illuminate\Database\Eloquent\Collection $collection)
    {
        /**
         * @var ElasticFilter $item
         */
        foreach ($collection as $item) {
            $this->slugs[] = $item->getSlug();
            if (!empty($item->getUrlSlug())) {
                $this->slugToUrl[ $item->getSlug() ] = $item->getUrlSlug();
                $this->urlToSlug[ $item->getUrlSlug() ] = $item->getSlug();
            }
        }
    }

    /**
     * @return mixed
     */
    public function getSlugs(): array
    {
        return $this->slugs;
    }

    /**
     * @return mixed
     */
    public function getSlugToUrl(): array
    {
        return $this->slugToUrl;
    }

    /**
     * @return mixed
     */
    public function getUrlToSlug(): array
    {
        return $this->urlToSlug;
    }
}
