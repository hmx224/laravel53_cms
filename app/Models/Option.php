<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    //评论是否需要审核
    const COMMENT_REQUIRE_PASS = 'COMMENT_REQUIRE_PASS';

    const TYPE_BOOLEAN = 1;
    const TYPE_TEXT = 2;
    const TYPE_TEXTAREA = 3;
    const TYPE_DATE = 4;
    const TYPE_DATETIME = 5;
    const TYPE_SINGLE = 6;
    const TYPE_MULTIPLE = 7;

    const TYPES = [
        1 => '布尔',
        2 => '文本',
        3 => '多行文本',
        4 => '日期',
        5 => '日期时间',
        6 => '单选',
        7 => '多选',
    ];

    protected $fillable = [
        'site_id',
        'code',
        'name',
        'value',
        'type',
        'option',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function typeName()
    {
        return array_key_exists($this->type, static::TYPES) ? static::TYPES[$this->type] : '';
    }

    /**
     * 根据编码获取值
     * @param $code
     * @return null
     */
    public static function getValue($code)
    {
        $option = Option::where('code', $code)->first();
        if ($option) {
            return $option->value;
        } else {
            return null;
        }
    }

    public function scopeOwns($query)
    {
        $query->where('site_id', Auth::user()->site_id);
    }
}
