<?php

namespace App\Models;

class Order extends BaseModule
{
    const TYPE_CHARGE = 1;
    const TYPE_BUY = 2;

    const TYPES = [
        1 => '充值',
        2 => '购买',
    ];

    const PAY_TYPE_APPLE = 1;
    const PAY_TYPE_ALIPAY = 2;
    const PAY_TYPE_WECHAT = 3;
    const PAY_TYPE_COIN = 4;

    const PAY_TYPES = [
        1 => '苹果内购',
        2 => '支付宝',
        3 => '微信支付',
        4 => '积分'
    ];

    const STATE_CANCELED = 0;
    const STATE_UNPAID = 1;
    const STATE_COMPLETED = 2;
    const STATE_WAIT_REFUND = 3;
    const STATE_ALREADY_REFUND = 4;

    const STATES = [
        0 => '已取消',
        1 => '待支付',
        2 => '已完成',
        3 => '待退款',
        4 => '已退款',
    ];

    protected $fillable = [
        'site_id',
        'refer_id',
        'refer_type',
        'code',
        'type',
        'title',
        'num',
        'sum',
        'pay_type',
        'transaction_id',
        'memo',
        'member_id',
        'user_id',
        'sort',
        'state',
    ];

    public function refer()
    {
        return $this->morphTo();
    }

    public function typeName()
    {
        return array_key_exists($this->type, static::TYPES) ? static::TYPES[$this->type] : '';
    }

    public function payName()
    {
        return array_key_exists($this->pay_type, static::PAY_TYPES) ? static::PAY_TYPES[$this->pay_type] : '';
    }

    public static function getTypes()
    {
        return [
            '' => '请选择',
            static::TYPE_CHARGE => '充值',
            static::TYPE_BUY => '购买',
        ];
    }

    public static function getPayTypes()
    {
        return [
            '' => '请选择',
            static::PAY_TYPE_APPLE => '苹果支付',
            static::PAY_TYPE_ALIPAY => '支付宝',
            static::PAY_TYPE_WECHAT => '微信支付',
            static::PAY_TYPE_COIN => '法商值',
        ];
    }

    public function scopeFilter($query, $filters)
    {
        $query->where(function ($query) use ($filters) {
            !empty($filters['id']) ? $query->where('id', $filters['id']) : '';
            !empty($filters['code']) ? $query->where('code', $filters['code']) : '';
            !empty($filters['title']) ? $query->where('title', 'like', '%' . $filters['title'] . '%') : '';
            !empty($filters['type']) ? $query->where('type', $filters['type']) : '';
            !empty($filters['pay_type']) ? $query->where('pay_type', $filters['pay_type']) : '';
            !empty($filters['mobile']) ? $query->whereHas('member', function ($query) use ($filters) {
                $query->where('mobile', $filters['mobile']);
            }) : '';
            isset($filters['state']) && $filters['state'] !== '' ? $query->where('state', $filters['state']) : '';
            !empty($filters['start_date']) ? $query->where('created_at', '>=', $filters['start_date'])
                ->where('created_at', '<=', $filters['end_date']) : '';
        });
    }

    public static function getCode()
    {
        return date('YmdHis') . mt_rand(1000, 9999);
    }
}
