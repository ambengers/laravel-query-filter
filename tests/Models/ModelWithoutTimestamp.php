<?php

namespace Ambengers\QueryFilter\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class ModelWithoutTimestamp extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}