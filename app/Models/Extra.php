<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    protected $fillable = [
        'site_id',
        'refer_id',
        'refer_type',
        'url',
        'clicks',
        'likes',
    ];

    public function refer()
    {
        return $this->morphTo();
    }
}