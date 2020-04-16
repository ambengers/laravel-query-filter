<?php

namespace Ambengers\QueryFilter\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Satire extends Model
{
    /**
     * The attributes that are guarded.
     *
     * @var array
     */
    protected $guarded = [];

    public function posts()
    {
        return $this->morphMany(Post::class, 'category');
    }
}
