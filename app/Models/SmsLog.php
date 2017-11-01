<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class SmsLog extends Model
{
    const STATE_SUCCESS = 1;
    const STATE_FAILURE = 2;

    const STATES = [
        1 => '成功',
        2 => '失败',
    ];

    protected $fillable = [
        'site_id',
        'mobile',
        'message',
        'state'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
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
        });
    }

    public function stateName()
    {
        return array_key_exists($this->state, static::STATES) ? static::STATES[$this->state] : '';
    }
}
?>