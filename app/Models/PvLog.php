<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PvLog extends Model
{
    protected $fillable = [
        'id',
        'site_id',
        'title',
        'url',
        'ip',
    ];
}
