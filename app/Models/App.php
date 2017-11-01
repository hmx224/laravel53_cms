<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    protected $fillable = [
        'name',
        'android_version',
        'android_force',
        'android_url',
        'ios_version',
        'ios_force',
        'ios_url',
        'logo_url',
        'username',
    ];
}
