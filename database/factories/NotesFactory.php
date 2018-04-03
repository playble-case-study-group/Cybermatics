<?php

use Faker\Generator as Faker;

$factory->define(App\notes::class, function (Faker $faker) {
    return [
        //
        'id' => $faker->unique()->randomNumber($nbDigits = 2),
        'user_id' => $faker->randomDigitNotNull,
        'day' => $faker->randomElement($array = array('1', '2', '3')),
        'created_at' => $faker->dateTime($max = 'now', $timezone = null),
        'note' => $faker->text($maxNbChars = 100)
    ];
});