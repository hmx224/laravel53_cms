<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UvLog extends Model
{
    protected $fillable = [
        'id',
        'site_id',
        'uvid',
        'browser',
        'os',
    ];
}
