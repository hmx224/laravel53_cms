<?php

namespace Modules\Vote\Models;

use App\Models\BaseModule;
use Auth;
use Exception;
use Gate;
use Illuminate\Database\Eloquent\SoftDeletes;
use Response;
use Request;

class Vote extends BaseModule
{
    use SoftDeletes;

    const STATE_DELETED = 0;
    const STATE_NORMAL = 1;
    const STATE_CANCELED = 2;
    const STATE_PUBLISHED = 9;

    const MULTIPLE_FALSE = 0;
    const MULTIPLE_TRUE = 1;

    const TOP_TRUE = 1;
    const TOP_FALSE = 0;

    const LINK_TYPE_NONE = 0;
    const LINK_TYPE_WEB = 1;

    const STATES = [
        0 => '已删除',
        1 => '未发布',
        2 => '已撤回',
        9 => '已发布',
    ];

    const STATE_PERMISSIONS = [
        0 => '@vote-delete',
        2 => '@vote-cancel',
        9 => '@vote-publish',
    ];

    const MULTIPLES = [
        0 => '单选',
        1 => '多选',
    ];

    protected $fillable = [
        'site_id',
        'title',
        'multiple',
        'image_url',
        'link_type',
        'link',
        'content',
        'begin_date',
        'end_date',
        'amount',
        'sort',
        'state',
        'member_id',
        'user_id',
        'published_at',
    ];

    protected $dates = ['published_at'];

    public function items()
    {
        return $this->morphMany(VoteItem::class, 'refer');
    }

    public function data()
    {
        return $this->hasMany(VoteData::class);
    }

    public static function getLinkTypes()
    {
        return [
            static::LINK_TYPE_NONE => '无',
            static::LINK_TYPE_WEB => '网址',
        ];
    }

    /**
     * 条件过滤
     *
     * @param $query
     * @param $filters
     */
    public function scopeFilter($query, $filters)
    {
        $query->where(function ($query) use ($filters) {
            empty($filters['title']) ?: $query->where('title', 'like', '%' . $filters['title'] . '%');
            empty($filters['start_date']) ?: $query->where('created_at', '>=', $filters['start_date']);
            empty($filters['end_date']) ?: $query->where('created_at', '<=', $filters['end_date']);
        });
        if (isset($filters['state'])) {
            if (!empty($filters['state'])) {
                $query->where('state', $filters['state']);
            } else if ($filters['state'] === strval(static::STATE_DELETED)) {
                $query->onlyTrashed();
            }
        }
    }

    public static function table()
    {
        $filters = Request::all();
        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $votes = static::owns()
            ->filter($filters)
            ->orderBy('sort', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $total = static::owns()
            ->filter($filters)
            ->count();

        $votes->transform(function ($vote) {
            $attributes = $vote->getAttributes();
            foreach ($vote->getDates() as $date) {
                $attributes[$date] = empty($vote->$date) ? '' : $vote->$date->toDateTimeString();
            }
            $attributes['tags'] = implode(',', $vote->tags()->pluck('name')->toArray());
            $attributes['state_name'] = $vote->stateName();
            return $attributes;
        });

        $ds = new \stdClass();
        $ds->rows = $votes;
        $ds->total = $total;

        return Response::json($ds);
    }

    /**
     * 排序
     */
    public static function sort()
    {
        $select_id = request('select_id');
        $place_id = request('place_id');
        $move_down = request('move_down');

        $select = self::find($select_id);
        $place = self::find($place_id);

        if (empty($select) || empty($place)) {
            return Response::json([
                'status_code' => 404,
                'message' => 'ID不存在',
            ]);
        }

        try {
            if ($move_down) {
                //下移
                $select->sort = $place->sort - 1;
                //减小最近100条记录的排序值
                self::where('sort', '<', $place->sort)
                    ->orderBy('sort', 'desc')
                    ->limit(100)
                    ->decrement('sort');
            } else {
                //上移
                $select->sort = $place->sort + 1;
                //增大最近100条记录的排序值
                self::where('sort', '>', $place->sort)
                    ->orderBy('sort', 'asc')
                    ->limit(100)
                    ->increment('sort');
            }
        } catch (Exception $e) {
            return Response::json([
                'status_code' => 500,
                'message' => $e->getMessage(),
            ]);
        }
        $select->save();

        return Response::json([
            'status_code' => 200,
            'message' => 'success',
        ]);
    }
}
