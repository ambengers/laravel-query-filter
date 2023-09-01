<?php

use Ambengers\QueryFilter\Tests\Models\ModelWithoutTimestamp;
use Faker\Generator as Faker;

$factory->define(ModelWithoutTimestamp::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
    ];
});
