<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    const ACTION_LOGIN = '登录系统';
    const ACTION_LOGOUT = '退出系统';
    const ACTION_CREATE = '添加';
    const ACTION_UPDATE = '修改';
    const ACTION_DELETE = '删除';
    const ACTION_STATE = '状态';

    protected $fillable = [
        'site_id',
        'refer_id',
        'refer_type',
        'action',
        'ip',
        'user_id',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function refer()
    {
        return $this->morphTo();
    }

    public function scopeOwns($query)
    {
        $query->where('site_id', Auth::user()->site_id);
    }

    public function scopeFilter($query, $filters)
    {
        $query->where(function ($query) use ($filters) {
            empty($filters['mobile']) ?: $query->where('mobile', $filters['mobile']);
            empty($filters['start_date']) ?: $query->where('created_at', '>=', $filters['start_date']);
            empty($filters['end_date']) ?: $query->where('created_at', '<=', $filters['end_date']);
            empty($filters['user_id']) ?: $query->whereHas('user', function ($query) use ($filters) {
                $query->where('id', $filters['user_id']);
            });
        });
    }

    public static function record($action, $refer_id = 0, $refer_type = '')
    {
        static::create([
            'site_id' => auth()->check() ? auth()->user()->site_id : 0,
            'action' => $action,
            'refer_id' => $refer_id,
            'refer_type' => $refer_type,
            'ip' => get_client_ip(),
            'user_id' => auth()->check() ? auth()->user()->id : 0,
        ]);
    }
}
