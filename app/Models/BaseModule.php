<?php

namespace App\Models;

use Auth;
use Cache;
use Carbon\Carbon;
use Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class BaseModule extends Model
{
    use SoftDeletes;

    const STATE_DELETED = 0;
    const STATE_PUBLISHED = 9;

    const STATES = [];

    const STATE_PERMISSIONS = [];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function extra()
    {
        return $this->morphOne(Extra::class, 'refer');
    }

    public function tags()
    {
        return $this->morphMany(Tag::class, 'refer');
    }

    public function items()
    {
        return $this->morphMany(Item::class, 'refer');
    }

    public function images()
    {
        return $this->items()->where('type', Item::TYPE_IMAGE)->orderBy('sort')->get();
    }

    public function audios()
    {
        return $this->items()->where('type', Item::TYPE_AUDIO)->orderBy('sort')->get();
    }

    public function videos()
    {
        return $this->items()->where('type', Item::TYPE_VIDEO)->orderBy('sort')->get();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'refer');
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'refer');
    }

    public function follows()
    {
        return $this->morphMany(Follow::class, 'refer');
    }

    public function order()
    {
        return $this->morphOne(Order::class, 'refer');
    }

    public function getCommentsCountAttribute()
    {
        return cache_remember($this->table . "-comment-$this->id", 1, function () {
            return $this->comments()->where('state', Comment::STATE_PASSED)->count();
        });
    }

    public function getFavoritesCountAttribute()
    {
        return cache_remember($this->table . "-favorite-$this->id", 1, function () {
            return $this->favorites()->count();
        });
    }

    public function getFollowsCountAttribute()
    {
        return cache_remember($this->table . "-follow-$this->id", 1, function () {
            return $this->follows()->count();
        });
    }

    public function getClicksCountAttribute()
    {
        return Cache::rememberForever($this->table . "-click-$this->id", function () {
            return $this->extra ? $this->extra->clicks : 0;
        });
    }

    public function getLikesCountAttribute()
    {
        return cache_remember($this->table . "-like-$this->id", 1, function () {
            return $this->extra ? $this->extra->likes : 0;
        });
    }

    public function incrementClick()
    {
        $count = Cache::increment($this->table . "-click-$this->id");
        //间隔回写数据库
        if ($count % env('CLICK_INTERVAL', 1) == 0) {
            if (empty($this->extra)) {
                $this->extra()->create([
                    'site_id' => $this->site_id,
                    'clicks' => $count,
                ]);
            } else {
                $this->extra->increment('clicks');
            }
        }
    }

    public function getStateNameAttribute()
    {
        return array_key_exists($this->state, static::STATES) ? static::STATES[$this->state] : '';
    }

    public function setCreatedAt($value)
    {
        if (in_array('sort', $this->fillable)) {
            $this->attributes['sort'] = strtotime($value);
        }
        return parent::setCreatedAt($value);
    }

    public function stateName()
    {
        return array_key_exists($this->state, static::STATES) ? static::STATES[$this->state] : '';
    }

    public function scopeOwns($query)
    {
        $query->where('site_id', Auth::user()->site_id);
    }

    public function scopeFilter($query, $filters)
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
            if (!empty($filters['state'])) {
                $query->where('state', $filters['state']);
            } else if ($filters['state'] === strval(static::STATE_DELETED)) {
                $query->onlyTrashed();
            }
        }
    }

    public static function getStateName($state)
    {
        return array_key_exists($state, static::STATES) ? static::STATES[$state] : '';
    }

    public static function state($input)
    {
        $ids = $input['ids'];
        $state = $input['state'];

        //判断是否有操作权限
        $permission = array_key_exists($state, static::STATE_PERMISSIONS) ? static::STATE_PERMISSIONS[$state] : '';
        if (!empty($permission) && Gate::denies($permission)) {
            return;
        }

        $items = static::withTrashed()
            ->whereIn('id', $ids)
            ->get();
        foreach ($items as $item) {
            $item->state = $state;
            $item->save();
            if ($state == static::STATE_DELETED) {
                $item->delete();
            } else if ($item->trashed()) {
                $item->restore();
            }
            if ($state == static::STATE_PUBLISHED) {
                $item->published_at = Carbon::now();
                $item->save();
            }
        }
    }

    public static function click($id)
    {
        $object = static::find($id);
        if (empty($object)) {
            return;
        }

        $object->incrementClick();
    }
}