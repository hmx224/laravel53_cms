<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class PushLog extends Model
{
    const STATE_SUCCESS = 1;
    const STATE_FAILURE = 2;

    const STATES = [
        1 => '成功',
        2 => '失败',
    ];

    const IOS_PUSH_PRODUCTION = 1;
    const IOS_PUSH_DEVELOPMENT = 2;
    const IOS_PUSH_NONE = 0;

    const IOS_PUSH_OPTIONS = [
        1 => '生产环境',
        2 => '开发环境',
        0 => '不推送',
    ];

    const ANDROID_PUSH_OPTIONS = [
        1 => '推送',
        0 => '不推送',
    ];

    protected $fillable = [
        'site_id',
        'refer_id',
        'refer_type',
        'title',
        'url',
        'send_no',
        'msg_id',
        'err_msg',
        'user_id',
        'state',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStateNameAttribute()
    {
        return array_key_exists($this->state, static::STATES) ? static::STATES[$this->state] : '';
    }

    public function scopeOwns($query)
    {
        $query->where('site_id', Auth::user()->site_id);
    }

    public function scopeFilter($query, $filters)
    {
        $query->where(function ($query) use ($filters) {
            empty($filters['start_date']) ?: $query->where('created_at', '>=', $filters['start_date']);
            empty($filters['end_date']) ?: $query->where('created_at', '<=', $filters['end_date']);
            empty($filters['user_id']) ?: $query->whereHas('user', function ($query) use ($filters) {
                $query->where('id', $filters['user_id']);
            });
        });
        if (isset($filters['state'])) {
            if (!empty($filters['state'])) {
                $query->where('state', $filters['state']);
            }
        }
    }
}
