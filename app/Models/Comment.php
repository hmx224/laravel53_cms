<?php

namespace App\Models;

use Auth;
use Gate;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends BaseModule
{
    use SoftDeletes;

    const STATE_DELETED = 0;
    const STATE_NORMAL = 1;
    const STATE_PASSED = 9;

    const STATES = [
        0 => '已删除',
        1 => '未审核',
        9 => '已审核',
    ];

    const TYPE_ARTICLE = 1;
    const TYPE_QUESTION = 2;

    const STATE_PERMISSIONS = [
        0 => '@comment-delete',
        9 => '@comment-pass',
    ];

    protected $fillable = [
        'site_id',
        'refer_id',
        'refer_type',
        'content',
        'likes',
        'ip',
        'member_id',
        'user_id',
        'state',
    ];

    public function refer()
    {
        return $this->morphTo();
    }

    public function scopeFilter($query, $filters)
    {
        $query->where(function ($query) use ($filters) {
            !empty($filters['refer_id']) ? $query->where('refer_id', $filters['refer_id']) : '';
            !empty($filters['refer_type']) ? $query->where('refer_type', urldecode($filters['refer_type'])) : '';
        });
        if (isset($filters['state'])) {
            if (!empty($filters['state'])) {
                $query->where('state', $filters['state']);
            } else if ($filters['state'] === strval(static::STATE_DELETED)) {
                $query->onlyTrashed();
            }
        }
    }
}