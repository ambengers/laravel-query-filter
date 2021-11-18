<?php

use Ambengers\QueryFilter\Tests\Models\Environment;
use Ambengers\QueryFilter\Tests\Models\Project;
use Faker\Generator as Faker;

$factory->define(Environment::class, function (Faker $faker) {
    return [
        'project_id' => factory(Project::class),
        'name' => $faker->sentence,
    ];
});
