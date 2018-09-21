<?php

use Faker\Generator as Faker;

$factory->define(Ambengers\QueryFilter\Tests\Models\Post::class, function (Faker $faker) {
    return [
        'subject'	=>	$faker->sentence,
        'body'		=>	$faker->paragraph,
    ];
});
