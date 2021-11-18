<?php

namespace Ambengers\QueryFilter\Tests\Filters;

use Ambengers\QueryFilter\AbstractQueryLoader;

class ProjectLoaders extends AbstractQueryLoader
{
    /**
     * Relationships that can be lazy/eager loaded
     *
     * @var array
     */
    protected $loadables = [
        'deployments',
    ];
}
