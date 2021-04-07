<?php
declare(strict_types=1);


namespace OrangeShadow\ElasticFilter\Repositories;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use OrangeShadow\ElasticFilter\Models\ElasticFilter;

/**
 * Class ElasticFilterRepository
 * @package OrangeShadow\ElasticFilter\Repositories
 */
class ElasticFilterRepository
{
    protected $validation = [
        'category'   => 'required',
        'slug'  => 'required',
        'index' => 'required',
        'sort'  => 'numeric|nullable',
    ];

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
        $validator = Validator::make($data, $this->validation);
        if ($validator->fails()) {
            new ValidationException($validator);
        }

        return ElasticFilter::create($data);
    }


    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $validator = Validator::make($data, $this->validation);
        if ($validator->fails()) {
            new ValidationException($validator);
        }

        return ElasticFilter::where('id', $id)
            ->update($data);
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
