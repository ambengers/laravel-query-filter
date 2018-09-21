<?php

namespace Ambengers\QueryFilter;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class AbstractQueryFilter
{
    use Concerns\InteractsWithRequest;

    /**
     * Query builder instance
     *
     * @var Illuminate\Database\Query\Builder
     */
    protected $builder;

    /**
     * List of searchable columns
     *
     * @var array
     */
    protected $searchableColumns = [];

    /**
     * Construct the object
     *
     * @param Request $request
     * @return  void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply the filters to the query
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
     * Perform a search from query
     *
     * @param  string $text
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function search($text = '')
    {
        if ($text == '') { return $this->builder; }

        // If we dont have anything in searchable columns
        // lets just return the builder to save query
        if (!$this->searchableColumns) { return $this->builder; }

        return $this->builder->where(function ($query) use ($text) {
            // Since we have a search filter, let's spin
            // through our list of searchable columns
            $this->performSearch($query, $text);
        });
    }

    /**
     * Iterate through searchable columns
     *
     * @param  Illuminate\Database\Eloquent\Builder $query
     * @param  string $searchText
     * @return void
     */
    protected function performSearch($query, $searchText)
    {
        foreach ($this->searchableColumns as $attribute => $value) {
            // If the value is an array, that means we want to search through a relationship.
            // We need to make sure that we send through the closure's query instance so we
            // can have an 'AND' query with nested queries wrapped within a parenthesis.
            if (is_array($value)) {
                $this->performRelationshipSearch($query, $attribute, $value, $searchText);
                continue;
            }

            $query->orWhere($value, 'like', "%{$searchText}%");
        }

        return $query;
    }

    /**
     * Search through related tables
     *
     * @param  Illuminate\Database\Eloquent\Builder $builder
     * @param  string $related
     * @param  array $columns
     * @param  string $text
     * @return Illuminate\Database\Eloquent\Builder
     */
    protected function performRelationshipSearch(Builder $builder, $related, $columns = '', $text = '')
    {
        if ($text == '') { return $builder; }

        $columns = is_array($columns) ? $columns : [$columns];

        return $builder->orWhereHas($related, function ($query) use ($columns, $text) {
            // Here, we want to make sure that we are grouping our orWhere
            // statement inside a where statement if incase the
            // relatonship is also running query scopes
            $query->where(function ($query) use ($columns, $text) {
                foreach ($columns as $key => $value) {
                    $query->orWhere($value, 'like', "%{$text}%");
                }
            });
        });
    }

    /**
     * Get the collection results after applying the filters
     *
     * @param  Builder $builder
     * @return Illuminate\Support\Collection
     */
    public function getCollection(Builder $builder)
    {
        $result = $this->apply($builder)->get();

        return $this->shouldSort() ? $this->sortCollection($result) : $result;
    }

    /**
     * Sort a filtered result
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
     * Get the paginated results after applying the filters
     *
     * @param  Builder $builder
     * @return Illuminate\Support\Collection
     */
    public function getPaginated(Builder $builder)
    {
        $result = $this->apply($builder)->get();

        $perPage = $this->input('per_page', 15);
        $page = $this->input('page', 1);

        return $this->paginate($result, $perPage, $page);
    }

    /**
     * Paginate a collection
     *
     * @param  mixed  $items
     * @param  integer $perPage
     * @param  integer  $page
     * @param  array   $options
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    protected function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        $itemsForPage = $items->forPage($page, $perPage);

        // If there is a sort key, then let's
        // sort the items for this page..
        if ($this->shouldSort()) {
            $itemsForPage = $this->sortCollection($itemsForPage);
        }

        return new LengthAwarePaginator(
            $itemsForPage, $items->count(), $perPage, $page, $options
        );
    }

    /**
     * Determine if sorting parameter is present in query string
     *
     * @return bool
     */
    public function shouldSort()
    {
        return $this->filled('sort');
    }

    /**
     * Determine if any pagination parameter is present in query string
     *
     * @return bool
     */
    public function shouldPaginate()
    {
    	return $this->has('page') || $this->has('per_page');
    }

}