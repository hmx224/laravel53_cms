<?php

namespace Modules\Activity\Models;

use App\Models\BaseModule;
use App\Models\Item;
use App\Models\Tag;
use Exception;
use Request;
use Response;


class Activity extends BaseModule
{
    const STATE_DELETED = 0;
    const STATE_NORMAL = 1;
    const STATE_CANCELED = 2;
    const STATE_PUBLISHED = 9;

    const STATES = [
        0 => '已删除',
        1 => '未发布',
        2 => '已撤回',
        9 => '已发布',
    ];

    const STATE_PERMISSIONS = [
        0 => '@activity-delete',
        2 => '@activity-cancel',
        9 => '@activity-publish',
    ];

    protected $table = 'activities';

    protected $fillable = ['site_id', 'category_id', 'type', 'title', 'subtitle', 'link_type', 'link', 'author', 'origin', 'tags', 'summary', 'image_url', 'video_url', 'content', 'top', 'amount', 'start_time', 'end_time', 'member_id', 'user_id', 'sort', 'state', 'published_at'];

    protected $dates = ['start_time', 'end_time', 'published_at'];

    protected $entities = ['member_id', 'user_id'];

    public function previous()
    {
        return static::where('site_id', $this->site_id)
            ->where('category_id', $this->category_id)
            ->where('state', $this->state)
            ->where('sort', '>', $this->sort)
            ->first();
    }

    public function next()
    {
        return static::where('site_id', $this->site_id)
            ->where('category_id', $this->category_id)
            ->where('state', $this->state)
            ->where('sort', '<', $this->sort)
            ->first();
    }

    public static function stores($input)
    {
        $input['state'] = static::STATE_NORMAL;

        $activity = static::create($input);

        //保存图片集
        if (isset($input['images'])) {
            Item::sync(Item::TYPE_IMAGE, $activity, $input['images']);

        }

        //保存音频集
        if (isset($input['audios'])) {
            Item::sync(Item::TYPE_AUDIO, $activity, $input['audios']);
        }

        //保存视频集
        if (isset($input['videos'])) {
            Item::sync(Item::TYPE_VIDEO, $activity, $input['videos']);
        }

        //保存标签
        if (isset($input['tags'])) {
            Tag::sync($activity, $input['tags']);
        }

        return $activity;
    }

    public static function updates($id, $input)
    {
        $activity = static::find($id);

        $activity->update($input);

        //保存图片集
        if (isset($input['images'])) {
            Item::sync(Item::TYPE_IMAGE, $activity, $input['images']);

        }

        //保存音频集
        if (isset($input['audios'])) {
            Item::sync(Item::TYPE_AUDIO, $activity, $input['audios']);
        }

        //保存视频集
        if (isset($input['videos'])) {
            Item::sync(Item::TYPE_VIDEO, $activity, $input['videos']);
        }

        //保存标签
        if (isset($input['tags'])) {
            Tag::sync($activity, $input['tags']);
        }

        return $activity;
    }

    public static function table()
    {
        $filters = Request::all();

        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $ds = new \stdClass();
        $activities = static::with('tags', 'member', 'user')
            ->filter($filters)
            ->orderBy('top', 'desc')
            ->orderBy('sort', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $ds->total = static::filter($filters)
            ->count();

        $activities->transform(function ($activity) {
            $attributes = $activity->getAttributes();

            //实体类型
            foreach ($activity->entities as $entity) {
                $entity_map = str_replace('_id', '_name', $entity);
                $entity = str_replace('_id', '', $entity);
                $attributes[$entity_map] = empty($activity->$entity) ? '' : $activity->$entity->name;
            }

            //日期类型
            foreach ($activity->dates as $date) {
                $attributes[$date] = empty($activity->$date) ? '' : $activity->$date->toDateTimeString();
            }
            $attributes['tags'] = implode(',', $activity->tags()->pluck('name')->toArray());
            $attributes['state_name'] = $activity->stateName();
            $attributes['created_at'] = empty($activity->created_at) ? '' : $activity->created_at->toDateTimeString();
            $attributes['updated_at'] = empty($activity->updated_at) ? '' : $activity->updated_at->toDateTimeString();
            return $attributes;
        });

        $ds->rows = $activities;

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

        if ($select->top && !$place->top) {
            return Response::json([
                'status_code' => 404,
                'message' => '置顶记录不允许移至普通位置',
            ]);
        }

        if (!$select->top && $place->top) {
            return Response::json([
                'status_code' => 404,
                'message' => '普通记录不允许移至置顶位置',
            ]);
        }

        $sort = $place->sort;
        try {
            if ($move_down) {
                //下移
                //增加移动区间的排序值
                self::owns()
                    ->where('category_id', $select->category_id)
                    ->where('sort', '>=', $place->sort)
                    ->where('sort', '<', $select->sort)
                    ->increment('sort');
            } else {
                //上移
                //减少移动区间的排序值
                self::owns()
                    ->where('category_id', $select->category_id)
                    ->where('sort', '>', $select->sort)
                    ->where('sort', '<=', $place->sort)
                    ->decrement('sort');
            }
        } catch (Exception $e) {
            return Response::json([
                'status_code' => 500,
                'message' => $e->getMessage(),
            ]);
        }
        $select->sort = $sort;
        $select->save();

        return Response::json([
            'status_code' => 200,
            'message' => 'success',
        ]);
    }

    public function data()
    {
        return $this->hasMany(ActivityData::class);
    }
}