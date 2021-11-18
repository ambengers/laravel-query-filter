<?php

namespace Ambengers\QueryFilter\Tests\Filters;

use Ambengers\QueryFilter\AbstractQueryFilter;

class ProjectFilters extends AbstractQueryFilter
{
    /**
     * Loader class
     *
     * @var string
     */
    protected $loader = ProjectLoaders::class;

    /**
     * List of searchable columns
     *
     * @var array
     */
    protected $searchableColumns = [
        'name',
        'deployments' => ['commit_hash'],
    ];

    /**
     * List of filters
     *
     * @var array
     */
    protected $filters = [
        //
    ];
}
