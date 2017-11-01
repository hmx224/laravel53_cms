<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    const ID_ROOT = 0;

    protected $fillable = [
        'site_id',
        'parent_id',
        'name',
        'url',
        'permission',
        'icon',
        'sort',
    ];

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id');
    }

    public function scopeOwns($query)
    {
        $query->where('site_id', Auth::user()->site_id);
    }

    public static function sort($menus, $parent_id = 0)
    {
        for ($i = 0; $i < count($menus); $i++) {
            $menu = Menu::find($menus[$i]->id);
            $menu->parent_id = $parent_id;
            $menu->sort = $i;
            $menu->save();

            if(isset($menus[$i]->children)) {
                static::sort($menus[$i]->children, $menu->id);
            }
        }
    }
}
