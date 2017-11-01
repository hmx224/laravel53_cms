<?php

namespace App\Models;

use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;
use Response;

class Tag extends Model
{
    const RECOMMEND = '推荐';

    protected $fillable = [
        'site_id',
        'refer_id',
        'refer_type',
        'name',
        'sort',
    ];

    public function refer()
    {
        return $this->morphTo();
    }

    public function setCreatedAt($value)
    {
        $this->attributes['sort'] = strtotime($value);
        return parent::setCreatedAt($value);
    }

    public function scopeOwns($query)
    {
        $query->where('site_id', Auth::user()->site_id);
    }

    public function scopeFilter($query, $filters)
    {
        $query->where(function ($query) use ($filters) {
            empty($filters['name']) ?: $query->where('name', $filters['name']);
            empty($filters['start_date']) ?: $query->where('created_at', '>=', $filters['start_date']);
            empty($filters['end_date']) ?: $query->where('created_at', '<=', $filters['end_date']);
            empty($filters['user_id']) ?: $query->whereHas('refer.user', function ($query) use ($filters) {
                $query->where('id', $filters['user_id']);
            });
            empty($filters['title']) ?: $query->whereHas('refer', function ($query) use ($filters) {
                $query->where('title', $filters['title']);
            });
        });
    }


    public static function sync($content, $tags)
    {
        if (is_array($tags)) {
            $content->tags()->delete();
            foreach ($tags as $tag) {
                $content->tags()->create([
                    'site_id' => $content->site_id,
                    'name' => $tag,
                ]);
            }
        }
    }

    public static function list($class)
    {
        return Tag::owns()
            ->select('name', DB::raw('count(*) as total'))
            ->where('refer_type', $class)
            ->groupBy('name')
            ->get();
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
                    ->where('name', $select->name)
                    ->where('sort', '>=', $place->sort)
                    ->where('sort', '<', $select->sort)
                    ->increment('sort');
            } else {
                //上移
                //减少移动区间的排序值
                self::owns()
                    ->where('name', $select->name)
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