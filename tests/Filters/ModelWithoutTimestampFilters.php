<?php

namespace Ambengers\QueryFilter\Tests\Filters;

use Ambengers\QueryFilter\AbstractQueryFilter;

class ModelWithoutTimestampFilters extends AbstractQueryFilter
{
    protected $searchableColumns = [
        'name',
    ];

    protected $filters = [
        //
    ];
}
