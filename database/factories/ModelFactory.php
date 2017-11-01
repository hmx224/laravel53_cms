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

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});


$factory->define(App\Models\Message::class, function (Faker\Generator $faker) {
    return [
        'site_id' => 1,
        'type' => 1,
        'title' => $faker->sentence,
        'content' => $faker->paragraph,
        'member_id' => $faker->randomElements(array(3, 4, 5))[0],
        'state' => \App\Models\Message::STATE_NORMAL,
    ];
});

$factory->define(App\Models\UvLog::class, function (Faker\Generator $faker) {
    return [
        'site_id' => 1,
        'uvid' => $faker->uuid,
        'browser' => $faker->randomElements(['IE', 'Firefox', 'Chrome', 'Safari', 'Edge', 'Other'])[0],
        'os' => $faker->randomElements(['Windows', 'Mac', 'Linux', 'Android', 'iOS', 'Other'])[0],
        'created_at' => $faker->dateTimeBetween('-7 days')
    ];
});

$factory->define(App\Models\IpLog::class, function (Faker\Generator $faker) {
    return [
        'site_id' => 1,
        'ip' => $faker->ipv4,
        'count' => $faker->numberBetween(1, 200),
        'country' => '中国',
        'province' => $faker->randomElements(['北京','天津','河北','山西','内蒙古','辽宁','吉林','黑龙江','上海','江苏','浙江','安徽','福建','江西','山东','河南','湖北','湖南','广东','广西','海南','重庆','四川','贵州','云南','西藏','陕西','甘肃','青海','宁夏','新疆','台湾'])[0],
        'city' => '武汉',
        'created_at' => $faker->dateTimeBetween('-7 days')
    ];
});

$factory->define(App\Models\Member::class, function (Faker\Generator $faker) {
    return [
        'site_id' => 1,
        'title' => $faker->sentence,
        'url' => $faker->url,
        'ip' => $faker->ipv4,
        'created_at' => $faker->dateTimeBetween('-7 days')
    ];
});

$factory->define(App\Models\Member::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'password' => $faker->password,
        'nick_name' => $faker->name,
        'avatar_url' => $faker->imageUrl(128, 128, 'people'),
        'ip' => $faker->ipv4,
        'created_at' => $faker->dateTimeBetween('-7 days')
    ];
});