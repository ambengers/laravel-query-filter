<?php

namespace Ambengers\QueryFilter;

trait QueryFilterable
{
	/**
     * Filter a query
     *
     * @param  Illuminnate\Database\Eloquent\Builder $query
     * @param  AbstractQueryFilter $filters
     * @return Illuminnate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, AbstractQueryFilter $filters)
    {
        if ($filters->shouldPaginate()) {
            return $filters->getPaginated($query);
        }

        return $filters->getCollection($query);
    }
}