<?php

namespace Ambengers\QueryFilter\Tests\Filters\Post;

use Illuminate\Database\Eloquent\Builder;

class Comment
{
    /**
     * Handle the filtering
     *
     * @param  Illuminate\Database\Eloquent\Builder $builder
     * @param  string|null  $value
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function __invoke(Builder $builder, $value = null)
    {
        $builder->whereHas('comments', function ($builder) use ($value) {
            $builder->whereId($value);
        });
    }
}
