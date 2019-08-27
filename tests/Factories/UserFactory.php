<?php

use Faker\Generator as Faker;
use Ambengers\QueryFilter\Tests\Models\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});