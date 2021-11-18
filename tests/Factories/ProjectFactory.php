<?php

use Faker\Generator as Faker;
use Ambengers\QueryFilter\Tests\Models\Project;

$factory->define(Project::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
    ];
});
