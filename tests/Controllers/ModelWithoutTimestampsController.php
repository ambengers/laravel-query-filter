<?php

namespace Ambengers\QueryFilter\Tests\Controllers;

use Ambengers\QueryFilter\Tests\Filters\ModelWithoutTimestampFilters;
use Ambengers\QueryFilter\Tests\Models\ModelWithoutTimestamp;
use Illuminate\Routing\Controller;

class ModelWithoutTimestampsController extends Controller
{
    public function index(ModelWithoutTimestampFilters $filters)
    {
        $models = ModelWithoutTimestamp::filter($filters);
        
        return response()->json($models);
    }
}
