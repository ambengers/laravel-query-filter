<?php

namespace Ambengers\QueryFilter\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Ambengers\QueryFilter\QueryFilterable;

class Comment extends Model
{
    use QueryFilterable;

	/**
     * The attributes that are guarded.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Post relationship
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
    	return $this->belongsTo(Post::class, 'post_id');
    }
}