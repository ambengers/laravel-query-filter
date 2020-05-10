<?php

use Faker\Generator as Faker;
use Ambengers\QueryFilter\Tests\Models\Satire;

$factory->define(Satire::class, function (Faker $faker) {
    return [
        'subject' => $faker->word,
    ];
});
