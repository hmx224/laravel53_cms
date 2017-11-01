<?php

namespace Modules\Survey\Models;

use App\Models\BaseModule;
use App\Models\Member;
use App\Models\Site;
use App\Models\Tag;
use Auth;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Request;
use Response;

class Survey extends BaseModule
{
    use SoftDeletes;

    const STATE_DELETED = 0;
    const STATE_NORMAL = 1;
    const STATE_CANCELED = 2;
    const STATE_PUBLISHED = 9;

    const MULTIPLE_FALSE = 0;
    const MULTIPLE_TRUE = 1;

    const TOP_FALSE = 0;
    const TOP_TRUE = 1;

    const LINK_TYPE_NONE = 0;
    const LINK_TYPE_WEB = 1;

    const STATES = [
        0 => '已删除',
        1 => '未发布',
        2 => '已撤回',
        9 => '已发布',
    ];

    const MULTIPLE = [
        0 => '单选',
        1 => '多选',
    ];

    const STATE_PERMISSIONS = [
        0 => '@survey-delete',
        2 => '@vote-cancel',
        9 => '@vote-publish',
    ];


    protected $fillable = [
        'site_id',
        'title',
        'type',
        'image_url',
        'description',
        'ip',
        'state',
        'user_id',
        'member_id',
        'begin_date',
        'end_date',
        'username',
        'amount',
        'multiple',
        'link',
        'link_type',
        'sort',
        'published_at',
        'top',
        'tags'
    ];

    protected $dates = ['published_at'];

    public function stateName()
    {
        switch ($this->state) {
            case static::STATE_NORMAL:
                return '未发布';
                break;
            case static::STATE_CANCELED:
                return '已撤回';
                break;
            case static::STATE_PUBLISHED:
                return '已发布';
                break;
            case static::STATE_DELETED:
                return '已删除';
                break;
        }
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
            empty($filters['id']) ?: $query->where('id', $filters['id']);
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
        $filter = Request::all();
        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $site_id = Auth::user()->site_id;

        $surveys = Survey::where('site_id', $site_id)
            ->filter($filter)
            ->orderBy('sort', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $count = Survey::where('site_id', $site_id)
            ->filter($filter)
            ->count();

        $surveys->transform(function ($survey) {
            $attributes = $survey->getAttributes();
            foreach ($survey->getDates() as $date) {
                $attributes[$date] = empty($survey->$date) ? '' : $survey->$date->toDateTimeString();
            }
            $attributes['state_name'] = $survey->stateName();
            $attributes['tags'] = implode(',', $survey->tags()->pluck('name')->toArray());
            $attributes['created_at'] = empty($survey->created_at) ? '' : $survey->created_at->toDateTimeString();
            $attributes['updated_at'] = empty($survey->updated_at) ? '' : $survey->updated_at->toDateTimeString();
            return $attributes;
        });

        $ds = new \stdClass();
        $ds->rows = $surveys;
        $ds->total = $count;
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


    public function data()
    {
        return $this->hasMany(SurveyData::class);
    }

    public function member()
    {
        return $this->hasOne(Member::class, 'id', 'member_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function subjects()
    {
        return $this->morphMany(Subject::class, 'refer');
    }

    public function tags()
    {
        return $this->morphMany(Tag::class, 'refer');
    }

}