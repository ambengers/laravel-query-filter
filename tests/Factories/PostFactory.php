<?php

use Faker\Generator as Faker;
use Ambengers\QueryFilter\Tests\Models\Comedy;

$factory->define(Ambengers\QueryFilter\Tests\Models\Post::class, function (Faker $faker) {
    $comedy = factory(Comedy::class)->create();

    return [
        'subject'       => $faker->sentence,
        'body'          => $faker->paragraph,
        'category_type' => $comedy->getMorphClass(),
        'category_id'   => $comedy->id,
    ];
});
