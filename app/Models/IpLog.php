<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpLog extends Model
{
    protected $fillable = [
        'id',
        'site_id',
        'ip',
        'count',
        'country',
        'province',
        'city',
    ];
}
