<?php

namespace Ambengers\QueryFilter;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Ambengers\QueryFilter\Exceptions\ObjectFilterNotInvokableException;

abstract class RequestQueryBuilder
{
    use Concerns\InteractsWithRequest;

    /**
     * Query builder instance.
     *
     * @var Illuminate\Database\Query\Builder
     */
    protected $builder;

    /**
     * List of searchable columns.
     *
     * @var array
     */
    protected $searchableColumns = [];

    /**
     * List of filters
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Construct the object.
     *
     * @param Request $request
     * @return  void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply the filters to the query.
     *
     * @param  Illuminate\Database\Query\Builder $builder
     * @return Illuminate\Database\Query\Builder $builder
     */
    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->all() as $key => $value) {
            $key = Str::camel($key);

            $this->filters = $this->camelKeys($this->filters)->toArray();

            if (array_key_exists($key, $this->filters)) {
                $this->objectBasedFilter($key, $value);

                continue;
            }

            if (method_exists($this, $key)) {
                $this->methodBasedFilter($key, $value);
            }
        }

        return $this->builder;
    }

    /**
     * Call the invokable object of the filter
     *
     * @param  string $key
     * @param  string $value
     * @return void
     */
    protected function objectBasedFilter($key, $value)
    {
        $object = new $this->filters[$key];

        if (is_callable($object)) {
            return $object($this->builder, $value);
        }

        throw new ObjectFilterNotInvokableException('Object filter must be callable.');
    }

    /**
     * Call method of the filter
     *
     * @param  string $key
     * @param  string $value
     * @return void
     */
    protected function methodBasedFilter($key, $value)
    {
        call_user_func_array([$this, $key], array_filter([$value]));
    }

    /**
     * Get the collection results after applying the filters.
     *
     * @param  Builder $builder
     * @return Illuminate\Support\Collection
     */
    public function getFilteredModelCollection(Builder $builder)
    {
        $result = $this->apply($builder)->get();

        if ((! $this instanceof AbstractQueryLoader) && $this->shouldSort()) {
            return $this->sortCollection($result);
        }

        return $result;
    }

    /**
     * Get model after applying the filters.
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    public function getFilteredModel(Builder $builder)
    {
        $builder = $builder->whereKey($builder->getModel()->getKey());

        return $this->apply($builder)->first();
    }

    /**
     * Sort a filtered result.
     *
     * @param  Illuminate\Support\Collection   $collection
     * @param  AbstractQueryFilter $filter
     * @return Illuminate\Support\Collection
     */
    protected function sortCollection(Collection $collection)
    {
        list($key, $order) = explode('|', $this->input('sort'));

        return $order === 'desc' ?
            $collection->sortByDesc($key) :
            $collection->sortBy($key);
    }

    /**
     * Transform array keys to camel case.
     *
     * @param  Illuminate\Support\Collection|array  $collection
     * @return Illuminate\Support\Collection
     */
    protected function camelKeys($collection)
    {
        return collect($collection)->keyBy(function ($item, $key) {
            return Str::camel($key);
        });
    }

    /**
     * Transform array elements to camel case.
     *
     * @param  Illuminate\Support\Collection|array  $collection
     * @return Illuminate\Support\Collection
     */
    protected function camelElements($collection)
    {
        return collect($collection)->map(function ($item) {
            return Str::camel($item);
        });
    }
}
