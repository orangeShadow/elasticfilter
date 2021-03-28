<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Repositories;


use Illuminate\Support\Collection;
use OrangeShadow\ElasticFilter\Models\ElasticFilter;

class ElasticFilterRepository
{
    /**
     * @param $id
     */
    public function get(int $id): ElasticFilter
    {

    }

    /**
     * @param array $data
     */
    public function insert(array $data): ElasticFilter
    {

    }


    /**
     * @param int $id
     * @param array $array
     */
    public function update(int $id, array $array): ElasticFilter
    {

    }

    /**
     * @param array $filter
     */
    public function search(array $filter = []): Collection
    {

    }
}
