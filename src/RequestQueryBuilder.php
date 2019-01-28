<?php

namespace Ambengers\QueryFilter;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

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

        foreach ($this->filters() as $method => $params) {
            $method = camel_case($method);
            if (method_exists($this, $method)) {
                call_user_func_array([$this, $method], array_filter([$params]));
            }
        }

        return $this->builder;
    }

    /**
     * Get the collection results after applying the filters.
     *
     * @param  Builder $builder
     * @return Illuminate\Support\Collection
     */
    public function getCollection(Builder $builder)
    {
        $result = $this->apply($builder)->get();

        if ((! $this instanceof AbstractQueryLoader) && $this->shouldSort()) {
            return $this->sortCollection($result);
        }

        return $result;
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
}
