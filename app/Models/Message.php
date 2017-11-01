<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    const STATE_DELETED = 0;
    const STATE_NORMAL = 1;

    const TYPE_SYSTEM = 1;

    protected $fillable = [
        'site_id',
        'type',
        'title',
        'content',
        'member_id',
        'state',
    ];

    public function typeName()
    {
        switch ($this->type) {
            case static::TYPE_SYSTEM:
                return '系统消息';
                break;
        }
    }

    public function stateName()
    {
        switch ($this->state) {
            case static::STATE_NORMAL:
                return '正常';
                break;
            case static::STATE_DELETED:
                return '已删除';
                break;
        }
    }

    public function scopeOwns($query)
    {
        $query->where('site_id', Auth::user()->site_id);
    }

    public static function count($site_id, $member_id)
    {
        return static::where('site_id', $site_id)
            ->where('member_id', $member_id)
            ->where('state', Message::STATE_NORMAL)
            ->count();
    }

    public static function send($site_id, $type, $word = [], $member_id)
    {
        $message = new Message();
        $message->site_id = $site_id;
        $message->type = $type;
        $message->title = vsprintf(config("site.$site_id.message.template.$type"), $word);
        $message->member_id = $member_id;
        $message->state = Message::STATE_NORMAL;
        $message->save();
    }
}