<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Models\Shop::class, function(Faker\Generator $faker){
    return [
        'width' => rand(1, 10),
        'height' => rand(1, 10)
    ];
});

$factory->define(App\Models\Robot::class, function(Faker\Generator $faker){
    return [
        'x' => rand(0, 9),
        'y' => rand(0, 9),
        'heading' => 'N',
        'commands' => 'LMMM'
    ];
});
