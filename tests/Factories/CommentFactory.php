<?php

use Faker\Generator as Faker;
use Ambengers\QueryFilter\Tests\Models\Post;
use Ambengers\QueryFilter\Tests\Models\Comment;

$factory->define(Comment::class, function (Faker $faker) {
    $post = factory(Post::class)->create();

    return [
        'post_id'       =>  $post->id,
        'body'          =>  $faker->paragraph,
        'approved_at'   =>  now(),
    ];
});
