<?php

namespace Ambengers\QueryFilter\Tests\Filters;

use Ambengers\QueryFilter\AbstractQueryFilter;

class PostObjectBasedFilters extends AbstractQueryFilter implements PostFilterInterface
{
    /**
     * Loader class
     *
     * @var string
     */
    protected $loader = PostLoader::class;

    /**
     * List of searchable columns
     *
     * @var array
     */
    protected $searchableColumns = [
        'subject',
        'body',
        'comments'  =>  ['body'],
    ];

    /**
     * List of filters
     *
     * @var array
     */
    protected $filters = [
        'comments'   =>  \Ambengers\QueryFilter\Tests\Filters\Post\Comment::class
    ];
}
