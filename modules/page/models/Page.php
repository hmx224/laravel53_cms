<?php

namespace Modules\Page\Models;

use App\Models\BaseModule;
use App\Models\Item;
use Exception;
use Request;
use Response;


class Page extends BaseModule
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
        0 => '@page-delete',
        2 => '@page-cancel',
        9 => '@page-publish',
    ];

    protected $table = 'pages';

    protected $fillable = ['site_id', 'title', 'subtitle', 'author', 'origin', 'slug', 'summary', 'image_url', 'video_url', 'audio_url', 'content', 'top', 'member_id', 'user_id', 'sort', 'state', 'published_at'];

    protected $dates = ['published_at'];

    protected $entities = ['member_id', 'user_id'];

    public function previous()
    {
        return static::where('site_id', $this->site_id)
            ->where('state', $this->state)
            ->where('sort', '>', $this->sort)
            ->orderBy('sort', 'desc')
            ->first();
    }

    public function next()
    {
        return static::where('site_id', $this->site_id)
            ->where('state', $this->state)
            ->where('sort', '<', $this->sort)
            ->orderBy('sort', 'desc')
            ->first();
    }

    public static function stores($input)
    {
        $input['state'] = static::STATE_NORMAL;

        $page = static::create($input);

        //保存图片集
        if (isset($input['images'])) {
            Item::sync(Item::TYPE_IMAGE, $page, $input['images']);

        }

        //保存音频集
        if (isset($input['audios'])) {
            Item::sync(Item::TYPE_AUDIO, $page, $input['audios']);
        }

        //保存视频集
        if (isset($input['videos'])) {
            Item::sync(Item::TYPE_VIDEO, $page, $input['videos']);
        }

        //保存标签
        if (isset($input['tags'])) {
            Tag::sync($page, $input['tags']);
        }

        return $page;
    }

    public static function updates($id, $input)
    {
        $page = static::find($id);

        $page->update($input);

        //保存图片集
        if (isset($input['images'])) {
            Item::sync(Item::TYPE_IMAGE, $page, $input['images']);

        }

        //保存音频集
        if (isset($input['audios'])) {
            Item::sync(Item::TYPE_AUDIO, $page, $input['audios']);
        }

        //保存视频集
        if (isset($input['videos'])) {
            Item::sync(Item::TYPE_VIDEO, $page, $input['videos']);
        }

        //保存标签
        if (isset($input['tags'])) {
            Tag::sync($page, $input['tags']);
        }

        return $page;
    }

    public static function table()
    {
        $filters = Request::all();

        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $ds = new \stdClass();
        $pages = static::with('user')
            ->filter($filters)
            ->orderBy('top', 'desc')
            ->orderBy('sort', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $ds->total = static::filter($filters)
            ->count();

        $pages->transform(function ($page) {
            $attributes = $page->getAttributes();

            //实体类型
            foreach ($page->entities as $entity) {
                $entity_map = str_replace('_id', '_name', $entity);
                $entity = str_replace('_id', '', $entity);
                $attributes[$entity_map] = empty($page->$entity) ? '' : $page->$entity->name;
            }

            //日期类型
            foreach ($page->dates as $date) {
                $attributes[$date] = empty($page->$date) ? '' : $page->$date->toDateTimeString();
            }
            $attributes['tags'] = implode(',', $page->tags()->pluck('name')->toArray());
            $attributes['state_name'] = $page->stateName();
            $attributes['created_at'] = empty($page->created_at) ? '' : $page->created_at->toDateTimeString();
            $attributes['updated_at'] = empty($page->updated_at) ? '' : $page->updated_at->toDateTimeString();
            return $attributes;
        });

        $ds->rows = $pages;

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
                    ->where('sort', '>=', $place->sort)
                    ->where('sort', '<', $select->sort)
                    ->increment('sort');
            } else {
                //上移
                //减少移动区间的排序值
                self::owns()
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
}