<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    const ID_ROOT = 0;

    const STATE_DISABLED = 0;
    const STATE_ENABLED = 1;

    const STATES = [
        1 => '已启用',
        0 => '已禁用',
    ];

    const LINK_TYPE_NONE = 0;
    const LINK_TYPE_WEB = 1;

    const LINK_TYPES = [
        0 => '无',
        1 => '网址',
    ];

    protected $fillable = [
        'site_id',
        'type',
        'code',
        'name',
        'parent_id',
        'module_id',
        'title',
        'author',
        'link',
        'summary',
        'image_url',
        'content',
        'state',
        'sort',
    ];

    public function getPathAttribute()
    {
        return $this->code;
    }

    public function getFullPathAttribute()
    {
        return empty($this->parent) ? $this->path : $this->parent->full_path . '/' . $this->path;
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'module_id');
    }

    public function scopeOwns($query)
    {
        if (Auth::user()->roles()->where('id', Role::ID_ADMIN)->exists()) {
            $query->where('site_id', Auth::user()->site_id);
        } else {
            $category_ids = Auth::user()->categories->pluck('id')->toArray();
            $query->where('site_id', Auth::user()->site_id)->whereIn('id', $category_ids);
        }
    }

    public function stateName()
    {
        return array_key_exists($this->state, static::STATES) ? static::STATES[$this->state] : '';
    }

    public static function findByFullPath($path)
    {
        $key = 'category-all';
        $categories = cache_remember($key, 1, function () {
            return Category::with('parent', 'children')
                ->where('state', Category::STATE_ENABLED)
                ->get();
        });

        $categories = $categories->filter(function ($category) use ($path) {
            return $category->full_path == $path;
        });
        if (count($categories) == 0) {
            return null;
        }
        return $categories->first();
    }

    public static function tree($state = '', $parent_id = 0, $module_id = 0, $include_parent = true)
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

        $parent = Category::find($parent_id);
        if (empty($parent)) {
            $root = new \stdClass();
            $root->id = $parent_id;
            $root->text = '所有栏目';
        } else {
            $root = new \stdClass();
            $root->id = $parent->id;
            $root->text = $parent->name;
        }

        static::getNodes($root, $categories);

        if ($include_parent) {
            return [$root];
        } else {
            return $root->nodes;
        }
    }

    public static function getNodes($parent, $categories)
    {
        foreach ($categories as $category) {
            if ($category->parent_id == $parent->id) {
                $node = new \stdClass();
                $node->id = $category->id;
                $node->text = $category->name;
                $node->tags = [$category->module->title];

                $parent->nodes[] = $node;
                static::getNodes($node, $categories);
            }
        }
    }
}
