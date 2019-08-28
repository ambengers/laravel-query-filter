<?php

namespace Ambengers\QueryFilter;

use Illuminate\Database\Eloquent\Builder;
use Ambengers\QueryFilter\Exceptions\MissingLoaderClassException;

abstract class AbstractQueryFilter extends RequestQueryBuilder
{
    /**
     * Perform a lazy/eager load from query string.
     *
     * @param  string $relations
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function load($relations = null)
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
    public function search($text = null)
    {
        if (! $text || ! $this->searchableColumns) {
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
     * @param  string $text
     * @return void
     */
    protected function performSearch($query, $text)
    {
        $searchable = explode(' ', $text);

        foreach ($searchable as $word) {
            foreach ($this->searchableColumns as $attribute => $value) {
                // If the value is an array, that means we want to search through a relationship.
                // We need to make sure that we send through the closure's query instance so we
                // can have an 'AND' query with nested queries wrapped within a parenthesis.
                is_array($value)
                    ? $this->performRelationsSearch($query, $attribute, $value, $word)
                    : $query->orWhere($value, 'like', "%{$word}%");
            }
        }

        return $query;
    }

    /**
     * Search through related tables.
     *
     * @param  Illuminate\Database\Eloquent\Builder $builder
     * @param  string                               $related
     * @param  array|string                         $columns
     * @param  string                               $text
     * @return Illuminate\Database\Eloquent\Builder
     */
    protected function performRelationsSearch(Builder $builder, $related, $columns, $text)
    {
        $columns = is_array($columns) ? $columns : [$columns];

        return $builder->orWhereHas($related, function ($query) use ($columns, $text) {
            // Here, we want to make sure that we are grouping our orWhere
            // statement inside a where statement if incase the
            // relatonship is also running query scopes
            $query->where(function ($query) use ($columns, $text) {
                foreach ($columns as $attribute => $value) {
                    is_array($value)
                        ? $this->performRelationsSearch($query, $attribute, $value, $text)
                        : $query->orWhere($value, 'like', "%{$text}%");
                }
            });
        });
    }

    /**
     * Apply an orderBy clause to the query.
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function sort()
    {
        $sorting = explode('|', $this->input('sort'));

        return ! in_array($sorting[0], $this->sortableColumns)
            ? $this->builder
            : $this->builder->orderBy(
                $sorting[0],
                isset($sorting[1]) ? $sorting[1] : 'asc'
            );
    }

    /**
     * Get the paginated results after applying the filters.
     *
     * @param  Illuminate\Database\Eloquent\Builder $builder
     * @return Illuminate\Support\Collection
     */
    public function paginate(Builder $builder)
    {
        return $this->apply($builder)
            ->paginate($this->input('per_page', 15));
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
