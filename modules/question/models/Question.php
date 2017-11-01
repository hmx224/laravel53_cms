<?php

namespace Modules\Question\Models;

use App\Models\BaseModule;
use App\Models\Item;
use Exception;
use Request;
use Response;


class Question extends BaseModule
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
        0 => '@question-delete',
        2 => '@question-cancel',
        9 => '@question-publish',
    ];

    protected $table = 'questions';

    protected $fillable = ['site_id', 'title', 'content', 'top', 'member_id', 'user_id', 'sort', 'state', 'published_at'];

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

        $question = static::create($input);

        //保存图片集
        if (isset($input['images'])) {
            Item::sync(Item::TYPE_IMAGE, $question, $input['images']);

        }

        //保存音频集
        if (isset($input['audios'])) {
            Item::sync(Item::TYPE_AUDIO, $question, $input['audios']);
        }

        //保存视频集
        if (isset($input['videos'])) {
            Item::sync(Item::TYPE_VIDEO, $question, $input['videos']);
        }

        //保存标签
        if (isset($input['tags'])) {
            Tag::sync($question, $input['tags']);
        }

        return $question;
    }

    public static function updates($id, $input)
    {
        $question = static::find($id);

        $question->update($input);

        //保存图片集
        if (isset($input['images'])) {
            Item::sync(Item::TYPE_IMAGE, $question, $input['images']);

        }

        //保存音频集
        if (isset($input['audios'])) {
            Item::sync(Item::TYPE_AUDIO, $question, $input['audios']);
        }

        //保存视频集
        if (isset($input['videos'])) {
            Item::sync(Item::TYPE_VIDEO, $question, $input['videos']);
        }

        //保存标签
        if (isset($input['tags'])) {
            Tag::sync($question, $input['tags']);
        }

        return $question;
    }

    public static function table()
    {
        $filters = Request::all();

        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $ds = new \stdClass();
        $questions = static::with('user')
            ->filter($filters)
            ->orderBy('top', 'desc')
            ->orderBy('sort', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $ds->total = static::filter($filters)
            ->count();

        $questions->transform(function ($question) {
            $attributes = $question->getAttributes();

            //实体类型
            foreach ($question->entities as $entity) {
                $entity_map = str_replace('_id', '_name', $entity);
                $entity = str_replace('_id', '', $entity);
                $attributes[$entity_map] = empty($question->$entity) ? '' : $question->$entity->name;
            }

            //日期类型
            foreach ($question->dates as $date) {
                $attributes[$date] = empty($question->$date) ? '' : $question->$date->toDateTimeString();
            }
            $attributes['tags'] = implode(',', $question->tags()->pluck('name')->toArray());
            $attributes['state_name'] = $question->stateName();
            $attributes['created_at'] = empty($question->created_at) ? '' : $question->created_at->toDateTimeString();
            $attributes['updated_at'] = empty($question->updated_at) ? '' : $question->updated_at->toDateTimeString();
            return $attributes;
        });

        $ds->rows = $questions;

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