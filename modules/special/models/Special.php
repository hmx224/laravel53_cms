<?php

namespace Modules\Special\Models;

use App\Models\BaseModule;
use App\Models\Category;
use App\Models\Item;
use Exception;
use Request;
use Response;


class Special extends BaseModule
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
        0 => '@special-delete',
        2 => '@special-cancel',
        9 => '@special-publish',
    ];

    protected $table = 'specials';

    protected $fillable = ['site_id', 'category_id', 'type', 'title', 'subtitle', 'link_type', 'link', 'author', 'origin', 'tags', 'summary', 'image_url', 'video_url', 'content', 'top', 'member_id', 'user_id', 'sort', 'state', 'published_at'];

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

        $special = static::create($input);

        //保存图片集
        if (isset($input['images'])) {
            Item::sync(Item::TYPE_IMAGE, $special, $input['images']);

        }

        //保存音频集
        if (isset($input['audios'])) {
            Item::sync(Item::TYPE_AUDIO, $special, $input['audios']);
        }

        //保存视频集
        if (isset($input['videos'])) {
            Item::sync(Item::TYPE_VIDEO, $special, $input['videos']);
        }

        //保存标签
        if (isset($input['tags'])) {
            Tag::sync($special, $input['tags']);
        }

        return $special;
    }

    public static function updates($id, $input)
    {
        $special = static::find($id);

        $special->update($input);

        //保存图片集
        if (isset($input['images'])) {
            Item::sync(Item::TYPE_IMAGE, $special, $input['images']);

        }

        //保存音频集
        if (isset($input['audios'])) {
            Item::sync(Item::TYPE_AUDIO, $special, $input['audios']);
        }

        //保存视频集
        if (isset($input['videos'])) {
            Item::sync(Item::TYPE_VIDEO, $special, $input['videos']);
        }

        //保存标签
        if (isset($input['tags'])) {
            Tag::sync($special, $input['tags']);
        }

        return $special;
    }

    public static function table()
    {
        $filters = Request::all();

        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $ds = new \stdClass();
        $specials = static::with('tags', 'member', 'user')
            ->filter($filters)
            ->orderBy('top', 'desc')
            ->orderBy('sort', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $ds->total = static::filter($filters)
            ->count();

        $specials->transform(function ($special) {
            $attributes = $special->getAttributes();

            //实体类型
            foreach ($special->entities as $entity) {
                $entity_map = str_replace('_id', '_name', $entity);
                $entity = str_replace('_id', '', $entity);
                $attributes[$entity_map] = empty($special->$entity) ? '' : $special->$entity->name;
            }

            //日期类型
            foreach ($special->dates as $date) {
                $attributes[$date] = empty($special->$date) ? '' : $special->$date->toDateTimeString();
            }
            $attributes['tags'] = implode(',', $special->tags()->pluck('name')->toArray());
            $attributes['state_name'] = $special->stateName();
            $attributes['created_at'] = empty($special->created_at) ? '' : $special->created_at->toDateTimeString();
            $attributes['updated_at'] = empty($special->updated_at) ? '' : $special->updated_at->toDateTimeString();
            return $attributes;
        });

        $ds->rows = $specials;

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

    public static function tree($state = '', $parent_id = 0, $module_id = 0, $show_parent = true)
    {
        $categories = Category::owns()
            ->where(function ($query) use ($state) {
                if (!empty($state)) {
                    $query->where('state', $state);
                }
            })
            ->where(function ($query) use ($module_id) {
                if (!empty($module_id)) {
                    $query->where('module_id', $module_id);
                }
            })
            ->orderBy('sort')
            ->get();

        $parents = Category::where('module_id', $module_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $arr = [];

        foreach ($parents as $parent) {
            if (empty($parent)) {
                $root = new \stdClass();
                $root->text = '所有栏目';
            } else {
                $root = new \stdClass();
                $root->id = 0;
                $root->time = date('Ym', strtotime($parent->created_at));
                $root->text = date('Ym', strtotime($parent->created_at));
            }
            static::getNodes($root, $categories);

            if (in_array($root, $arr) == false) {
                $arr[] = $root;
            }
        }

        if ($show_parent) {
            return $arr;
        } else {
            return $root->nodes;
        }
    }

    public static function getNodes($parent, $categories)
    {
        foreach ($categories as $category) {
            $time = date('Ym', strtotime($category->created_at));

            if ($time == $parent->text) {
                $node = new \stdClass();
                $node->id = $category->id;
                $node->text = $category->name;
                $node->time = '';

                $parent->nodes[] = $node;
                static::getNodes($node, $categories);
            }
        }
    }
}