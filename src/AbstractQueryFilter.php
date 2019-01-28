<?php

namespace Ambengers\QueryFilter;

use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Ambengers\QueryFilter\Exceptions\MissingLoaderClassException;

abstract class AbstractQueryFilter extends RequestQueryBuilder
{
    /**
     * Perform a lazy/eager load from query string.
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function load($relations = '')
    {
        if (! $relations) {
            return $this->builder;
        }

        if (! $this->loader) {
            throw new MissingLoaderClassException(
                'Loader class is not defined on this filter instance.'
            );
        }

        return $this->newLoaderInstance()
            ->setEloquentBuilder($this->builder)
            ->load($relations);
    }

    /**
     * Get a new loader class instance.
     *
     * @return Ambengers\QueryFilter\AbstractQueryLoader
     */
    protected function newLoaderInstance()
    {
        return new $this->loader($this->request);
    }

    /**
     * Perform a search from query string.
     *
     * @param  string $text
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function search($text = '')
    {
        if ($text == '') {
            return $this->builder;
        }

        // If we dont have anything in searchable columns
        // lets just return the builder to save query
        if (! $this->searchableColumns) {
            return $this->builder;
        }

        return $this->builder->where(function ($query) use ($text) {
            // Since we have a search filter, let's spin
            // through our list of searchable columns
            $this->performSearch($query, $text);
        });
    }

    /**
     * Iterate through searchable columns.
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
     * Search through related tables.
     *
     * @param  Illuminate\Database\Eloquent\Builder $builder
     * @param  string $related
     * @param  array $columns
     * @param  string $text
     * @return Illuminate\Database\Eloquent\Builder
     */
    protected function performRelationshipSearch(Builder $builder, $related, $columns = '', $text = '')
    {
        if ($text == '') {
            return $builder;
        }

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
     * Get the paginated results after applying the filters.
     *
     * @param  Builder $builder
     * @return Illuminate\Support\Collection
     */
    public function getPaginated(Builder $builder)
    {
        $result = $this->apply($builder)->get();

        return $this->paginate(
            $result,
            $this->input('per_page', 15),
            $this->input('page', 1)
        );
    }

    /**
     * Paginate a collection.
     *
     * @param  mixed  $items
     * @param  int $perPage
     * @param  int  $page
     * @param  array   $options
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    protected function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        $items = $this->shouldSort() ? $this->sortCollection($items) : $items;

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            $options
        );
    }

    /**
     * Determine if sorting parameter is present in query string.
     *
     * @return bool
     */
    public function shouldSort()
    {
        return $this->filled('sort');
    }

    /**
     * Determine if any pagination parameter is present in query string.
     *
     * @return bool
     */
    public function shouldPaginate()
    {
        return $this->has('page') || $this->has('per_page');
    }
}
