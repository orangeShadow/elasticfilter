<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Url;

use OrangeShadow\ElasticFilter\IndexConfig;
use OrangeShadow\ElasticFilter\Models\ElasticFilter;
use OrangeShadow\ElasticFilter\Repositories\ElasticFilterRepository;

/**
 * Class AggsFieldHelper
 * @package OrangeShadow\ElasticFilter\Url
 */
class AggsFieldHelper
{
    protected $slugs = [];
    protected $slugToUrl = [];
    protected $urlToSlug = [];

    /**
     * AggsFieldHelper constructor.
     * @param string $category
     * @param IndexConfig $config
     */
    public function __construct(string $category, IndexConfig $config)
    {
        $repo = new ElasticFilterRepository();
        $collection = $repo->search([
            'category' => $category,
            'index'    => $config->getName()
        ]);

        /**
         * @var ElasticFilter $item
         */
        foreach ($collection as $item) {
            $this->slugs[] = $item->getSlug();
            if (!empty($item->getUrlSlug())) {
                $this->slugToUrl[ $item->getSlug() ] = $item->getUrlSlug() ?: [];
                $this->urlToSlug[ $item->getUrlSlug() ] = $item->getSlug() ?: [];
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
