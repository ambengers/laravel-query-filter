<?php

namespace Ambengers\QueryFilter;

trait QueryFilterable
{
    /**
     * Filter a query
     *
     * @param  Illuminnate\Database\Eloquent\Builder $query
     * @param  Ambengers\QueryFilter\RequestQueryBuilder $filters
     * @return Illuminnate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, RequestQueryBuilder $filters)
    {
        if ((! $filters instanceof AbstractQueryLoader) && $filters->shouldPaginate()) {
            return $filters->getPaginated($query);
        }

        return $filters->getCollection($query);
    }
}
