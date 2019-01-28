<?php

namespace Ambengers\QueryFilter\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Ambengers\QueryFilter\QueryFilterable;

class Post extends Model
{
    use QueryFilterable;

    /**
     * The attributes that are guarded.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Comments relationship
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
}
