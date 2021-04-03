<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Repositories;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use OrangeShadow\ElasticFilter\Models\ElasticFilter;

class ElasticFilterRepository
{
    /**
     * @param $id
     * @return ElasticFilter
     *
     * @throws ModelNotFoundException
     */
    public function get(int $id): ElasticFilter
    {
        return ElasticFilter::findOrFail($id);
    }

    /**
     * @param array $data
     */
    public function insert(array $data): ElasticFilter
    {
        return ElasticFilter::create($data);
    }


    /**
     * @param int $id
     * @param array $array
     * @return bool
     */
    public function update(int $id, array $array): bool
    {
        return ElasticFilter::where('id', $id)
            ->update($array);
    }

    /**
     * @param array $filter
     * @return Collection
     */
    public function search(array $filter = []): Collection
    {
        return ElasticFilter::search($filter)->get();
    }
}
