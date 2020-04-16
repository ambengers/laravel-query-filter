<?php

use Faker\Generator as Faker;
use Ambengers\QueryFilter\Tests\Models\Comedy;

$factory->define(Comedy::class, function (Faker $faker) {
    return [
        'subject' => $faker->word,
    ];
});
