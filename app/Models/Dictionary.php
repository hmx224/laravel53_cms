<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Dictionary extends Model
{
    protected $fillable = [
        'site_id',
        'parent_id',
        'code',
        'name',
        'value',
        'sort',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeOwns($query)
    {
        $query->where('site_id', Auth::user()->site_id);
    }

    public static function tree($state = '', $parent_id = 0, $show_parent = true)
    {
        $dictionaries = static::owns()
            ->where(function ($query) use ($state) {
                if (!empty($state)) {
                    $query->where('state', $state);
                }
            })
            ->orderBy('sort')
            ->get();

        $parent = static::find($parent_id);
        if (empty($parent)) {
            $root = new \stdClass();
            $root->id = $parent_id;
            $root->text = '所有字典';
        } else {
            $root = new \stdClass();
            $root->id = $parent->id;
            $root->text = $parent->name;
        }

        static::getNodes($root, $dictionaries);

        if ($show_parent) {
            return [$root];
        } else {
            return $root->nodes;
        }
    }

    public static function getNodes($parent, $dictionaries)
    {
        foreach ($dictionaries as $dictionary) {
            if ($dictionary->parent_id == $parent->id) {
                $node = new \stdClass();
                $node->id = $dictionary->id;
                $node->text = $dictionary->name;

                $parent->nodes[] = $node;
                static::getNodes($node, $dictionaries);
            }
        }
    }
}
