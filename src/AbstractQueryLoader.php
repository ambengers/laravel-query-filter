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

        $relations = collect(
            $this->keysToCamelCase(
                $this->parseRelations(explode(',', $relations))
            )
        )->intersectByKeys(
            $this->elementsToCamelCase($this->loadables)->flip()
        )->toArray();

        return $this->builder->with($relations);
    }

    /**
     * Parse the relations out of the given array
     *
     * @param  array  $relations
     * @return array
     */
    protected function parseRelations(array $relations)
    {
        $results = [];

        foreach ($relations as $key => $relation) {
            // If the relation contains a pipe symbol, that means our request wanted to have constraints on the
            // eager loaded relationship. We will need to save the relation as the key and provide the parsed
            // constraints as a value. Parsed constraints will be a closure that eloquent builder can run.
            if (Str::contains($relation, '|')) {
                list($relation, $constraints) = explode('|', $relation);

                $results[$relation] = $this->parseConstraints(explode(',', $constraints));

                continue;
            }

            $results[$relation] = function () {
                // Nothing to do here..
            };
        }

        return $results;
    }

    /**
     * Parse the constraints into callable query constrains
     *
     * @param  array  $constraints
     * @return callable
     */
    protected function parseConstraints(array $constraints)
    {
        return function ($query) use ($constraints) {
            foreach ($constraints as $constraint) {
                $constraint = Str::camel($constraint);

                $query->$constraint();
            }
        };
    }

    /**
     * Transform array keys to camel case
     *
     * @param  Illuminate\Support\Collection|array  $collection
     * @return Illuminate\Support\Collection
     */
    protected function keysToCamelCase($collection)
    {
        return collect($collection)->flatMap(function ($item, $key) {
            return [Str::camel($key) => $item];
        });
    }

    /**
     * Transform array elements to camel case.
     *
     * @param  Illuminate\Support\Collection|array  $collection
     * @return Illuminate\Support\Collection
     */
    protected function elementsToCamelCase($collection)
    {
        return collect($collection)->map(function ($item) {
            return Str::camel($item);
        });
    }
}
