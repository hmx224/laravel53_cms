<?php

namespace Modules\Video\Models;

use App\Models\BaseModule;
use App\Models\Item;
use App\Models\Tag;
use Exception;
use Request;
use Response;

class Video extends BaseModule
{
    const STATE_DELETED = 0;
    const STATE_NORMAL = 1;
    const STATE_CANCELED = 2;
    const STATE_PUBLISHED = 9;

    const TOP_FALSE = 0;
    const TOP_TRUE = 1;

    const TOP = '置顶';

    const PAGE_NUM = 18;

    const DEFAULT_STATE = -1; //切换网格标识
    const DEFAULT_STATE_LIST = -2; //切换列表标识

    const STATES = [
        0 => '已删除',
        1 => '未发布',
        2 => '已撤回',
        9 => '已发布',
    ];

    const STATE_PERMISSIONS = [
        0 => '@video-delete',
        2 => '@video-cancel',
        9 => '@video-publish',
    ];

    protected $table = 'videos';

    protected $fillable = ['site_id', 'category_id', 'title', 'type', 'subtitle', 'link', 'link_type', 'anthor', 'origin', 'tags', 'summary', 'image_url', 'video_url', 'content', 'top', 'member_id', 'user_id', 'sort', 'state', 'published_at'];

    protected $dates = ['published_at'];

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

        $video = static::create($input);

        //保存图片集
        if (isset($input['images'])) {
            Item::sync(Item::TYPE_IMAGE, $video, $input['images']);

        }

        //保存音频集
        if (isset($input['audios'])) {
            Item::sync(Item::TYPE_AUDIO, $video, $input['audios']);
        }

        //保存视频集
        if (isset($input['videos'])) {
            Item::sync(Item::TYPE_VIDEO, $video, $input['videos']);
        }

        //保存标签
        if (isset($input['tags'])) {
            Tag::sync($video, $input['tags']);
        }

        return $video;
    }

    public static function updates($id, $input)
    {
        $video = static::find($id);

        $video->update($input);

        //保存图片集
        if (isset($input['images'])) {
            Item::sync(Item::TYPE_IMAGE, $video, $input['images']);

        }

        //保存音频集
        if (isset($input['audios'])) {
            Item::sync(Item::TYPE_AUDIO, $video, $input['audios']);
        }

        //保存视频集
        if (isset($input['videos'])) {
            Item::sync(Item::TYPE_VIDEO, $video, $input['videos']);
        }

        //保存标签
        if (isset($input['tags'])) {
            Tag::sync($video, $input['tags']);
        }

        return $video;
    }

    public static function table()
    {
        $filters = Request::all();

        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $ds = new \stdClass();
        $videos = static::with('tags', 'member', 'user')
            ->filter($filters)
            ->orderBy('top', 'desc')
            ->orderBy('sort', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $ds->total = static::filter($filters)
            ->count();

        $videos->transform(function ($video) {
            $attributes = $video->getAttributes();

            //实体类型
            foreach ($video->entities as $entity) {
                $entity_map = str_replace('_id', '_name', $entity);
                $entity = str_replace('_id', '', $entity);
                $attributes[$entity_map] = empty($video->$entity) ? '' : $video->$entity->name;
            }

            //日期类型
            foreach ($video->dates as $date) {
                $attributes[$date] = empty($video->$date) ? '' : $video->$date->toDateTimeString();
            }
            $attributes['tags'] = implode(',', $video->tags()->pluck('name')->toArray());
            $attributes['state_name'] = $video->stateName();
            $attributes['created_at'] = empty($video->created_at) ? '' : $video->created_at->toDateTimeString();
            $attributes['updated_at'] = empty($video->updated_at) ? '' : $video->updated_at->toDateTimeString();
            return $attributes;
        });

        $ds->rows = $videos;

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

    // 网格列表输出
    public function getVideoList($page, $state, $filters)
    {
        if (!empty($filters)) {
            $videos = Video::with('tags', 'member', 'user')
                ->filters($filters)
                ->orderBy('top', 'desc')
                ->orderBy('sort', 'desc')
                ->paginate($page);
            return $videos;
        } else {
            $filters = [];
            $filters['state'] = $state;
            $videos = Video::with('tags', 'member', 'user')
                ->filters($filters)
                ->orderBy('top', 'desc')
                ->orderBy('sort', 'desc')
                ->paginate($page);
            return $videos;
        }
    }

    public function scopeFilters($query, $filters)
    {
        $query->where(function ($query) use ($filters) {
            empty($filters['id']) ?: $query->where('id', $filters['id']);
            empty($filters['category_id']) ?: $query->where('category_id', $filters['category_id']);
            empty($filters['title']) ?: $query->where('title', 'like', '%' . $filters['title'] . '%');
            empty($filters['start_date']) ?: $query->where('created_at', '>=', $filters['start_date']);
            empty($filters['end_date']) ?: $query->where('created_at', '<=', $filters['end_date']);
            empty($filters['user_name']) ?: $query->whereHas('user', function ($query) use ($filters) {
                $query->where('name', $filters['user_name']);
            });
        });

        if (isset($filters['state'])) {
            if ($filters['state'] === strval(static::STATE_DELETED)) {
                $query->onlyTrashed();
            } else if ($filters['state'] == strval(static::DEFAULT_STATE)) {
                $query->where('state', '>', $filters['state']);
            } else {
                $query->where('state', $filters['state']);
            }
        }
    }

    //获取标签名
    public function getTagName($id)
    {
        $video = Video::with('tags')->find($id);
        if ($video) {
            foreach ($video->tags as $tag) {
                return $tag->name;
            }
        } else {
            return null;
        }
    }
}