<?php

namespace Modules\Activity\Models;

use App\Models\BaseModule;
use Response;

class ActivityData extends BaseModule
{
    protected $table = 'activity_data';

    protected $fillable = [
        'activity_id',
        'person_name',
        'person_mobile',
        'member_id',
        'IP',
    ];


    public static function table($activity_id)
    {
        $data = ActivityData::with('member')
            ->where('activity_id', $activity_id)
            ->orderBy('id', 'desc')
            ->get();

        $data->transform(function ($item) {
            return [
                'id' => $item->id,
                'person_name' => $item->person_name,
                'person_mobile' => $item->person_mobile,
                'nick_name' => $item->member->nick_name,
                'ip' => $item->ip,
                'created_at' => empty($item->created_at) ? '' : $item->created_at->toDateTimeString()
            ];
        });

        $ds = new \stdClass();

        $ds->data = $data;

        return Response::json($ds);
    }

}
