<?php

namespace Ambengers\QueryFilter;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractQueryLoader extends RequestQueryBuilder
{
    /**
     * Relationships that can be lazy/eager loaded.
     *
     * @var array
     */
    protected $loadables = [];

    /**
     * Set the builder instance.
     *
     * @param Illuminate\Database\Eloquent\Builder $builder
     */
    public function setEloquentBuilder(Builder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * Load relations based on the given query parameters.
     *
     * @param  Illuminate\Database\Eloquent\Builder $builder
     * @param  string $relations
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function load($relations = '')
    {
        if (! $relations) {
            return $this->builder;
        }

        $relations = collect($this->elementsToCamelCase($this->loadables))->intersect(
            $this->elementsToCamelCase(explode(',', $relations))
        )->toArray();

        return $this->builder->with($relations);
    }

    /**
     * Transform array elements to camel case.
     *
     * @param  array  $arr
     * @return Illuminate\Support\Collection
     */
    protected function elementsToCamelCase(array $arr)
    {
        return collect($arr)->map(function ($element) {
            return Str::camel($element);
        });
    }
}
