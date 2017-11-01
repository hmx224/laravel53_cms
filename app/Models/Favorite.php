<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'site_id',
        'category_id',
        'refer_id',
        'refer_type',
        'title',
        'member_id',
    ];

    public function refer()
    {
        return $this->morphTo();
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public static function count($site_id, $member_id)
    {
        return static::where('site_id', $site_id)
            ->where('member_id', $member_id)
            ->count();
    }
}
