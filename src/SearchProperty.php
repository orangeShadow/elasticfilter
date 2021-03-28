<?php
declare(strict_types=1);

namespace OrangeShadow\ElasticFilter;

/**
 * Parameter object for searching and aggregation
 *
 * Class SearchProperties
 * @package App\ElasticSearch\Properties
 */
class SearchProperty
{
    /**
     * Needed page
     *
     * @var int $page
     */
    private $page = 1;

    /**
     * Selected data from elastic
     *
     * @var array
     */
    private $source = [];

    /**
     * Total element for fetching
     *
     * @var int $size
     */
    private $size = 10000;

    /**
     * Sorting [$sort => $direction]
     *
     * @var array $sort
     */
    private $sort;

    /**
     * @var array $queryParams
     */
    private $queryParams;

    /**
     * SearchProperty constructor.
     * @param array $queryParams
     */
    public function __construct(array $queryParams = [])
    {
        $this->setQueryParams($queryParams);
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @param array $queryParams
     * @param self
     */
    public function setQueryParams(array $queryParams): self
    {
        $this->queryParams = $queryParams;

        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @param self
     */
    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @param self
     */
    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Add sort
     *
     * @param string $sortField
     * @param string $direction = asc [asc|direction]
     *
     * @return self
     */
    public function addSort(string $sortField, string $direction = 'asc'): self
    {
        $this->sort[] = [$sortField => strtolower($direction)];

        return $this;
    }

    /**
     * @param array $sort
     * @return $this
     */
    public function setSort(array $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @return array
     */
    public function getSource(): ?array
    {
        return $this->source;
    }

    /**
     * @param array|null $source
     * @return self
     */
    public function setSource(?array $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Shift on searching
     *
     * @return int
     */
    public function getFrom(): int
    {
        return ($this->getPage() - 1) * $this->getSize();
    }
}
