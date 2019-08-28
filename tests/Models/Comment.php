<?php

namespace Ambengers\QueryFilter\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are guarded.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['approved_at'];

    /**
     * Post relationship
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * User relationship
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope the query to approved
     *
     * @param  Illuminate\Database\Eloquent\Builder $query
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }
}
