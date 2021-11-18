<?php

use Ambengers\QueryFilter\Tests\Models\Deployment;
use Ambengers\QueryFilter\Tests\Models\Environment;
use Faker\Generator as Faker;

$factory->define(Deployment::class, function (Faker $faker) {
    return [
        'environment_id' => factory(Environment::class),
        'commit_hash' => $faker->uuid,
    ];
});
