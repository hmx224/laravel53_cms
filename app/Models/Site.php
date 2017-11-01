<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    const ID_DEFAULT = 1;

    protected $fillable = [
        'name',
        'title',
        'domain',
        'directory',
        'default_theme_id',
        'mobile_theme_id',
        'jpush_app_key',
        'jpush_app_secret',
        'wechat_app_id',
        'wechat_secret',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function default_theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function mobile_theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public static function stores($input)
    {
        $site_id = Auth::user()->site_id;
        $input['user_id'] = Auth::user()->id;

        $site = self::create($input);

        //添加站点时，添加默认菜单
        $parent = $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => Menu::ID_ROOT,
            'name' => '内容管理',
            'url' => '#',
            'icon' => 'fa-edit',
            'sort' => 0
        ]);

        $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => $parent->id,
            'name' => '文章管理',
            'url' => '/admin/articles',
            'permission' => '@article',
            'icon' => 'fa-file-o',
            'sort' => 0
        ]);

        $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => $parent->id,
            'name' => '单页管理',
            'url' => '/admin/pages',
            'permission' => '@page',
            'icon' => 'fa-file-o',
            'sort' => 1
        ]);

        $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => $parent->id,
            'name' => '问答管理',
            'url' => '/admin/questions',
            'permission' => '@question',
            'icon' => 'fa-question-circle',
            'sort' => 2
        ]);

        $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => $parent->id,
            'name' => '投票管理',
            'url' => '/admin/votes',
            'permission' => '@vote',
            'icon' => 'fa-file-o',
            'sort' => 3
        ]);

        $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => $parent->id,
            'name' => '专题管理',
            'url' => '/admin/specials',
            'permission' => '@special',
            'icon' => 'fa-flag-o',
            'sort' => 4
        ]);

        $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => $parent->id,
            'name' => '评论管理',
            'url' => '/admin/comments',
            'permission' => '@comment',
            'icon' => 'fa-comment-o',
            'sort' => 5
        ]);

        $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => $parent->id,
            'name' => '标签管理',
            'url' => '/admin/tags',
            'permission' => '@tag',
            'icon' => 'fa-tags',
            'sort' => 6
        ]);

        $parent = $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => Menu::ID_ROOT,
            'name' => '会员管理',
            'url' => '#',
            'icon' => 'fa-user',
            'sort' => 1
        ]);

        $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => $parent->id,
            'name' => '会员管理',
            'url' => '/admin/members',
            'permission' => '@member',
            'icon' => 'fa-user-o',
            'sort' => 1
        ]);

        $parent = $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => Menu::ID_ROOT,
            'name' => '日志查询',
            'url' => '#',
            'icon' => 'fa-calendar',
            'sort' => 0
        ]);

        $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => $parent->id,
            'name' => '操作日志',
            'url' => '/admin/users/logs',
            'permission' => '@log',
            'icon' => 'fa-user-o',
            'sort' => 0
        ]);

        $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => $parent->id,
            'name' => '推送日志',
            'url' => '/admin/push/logs',
            'permission' => '@log',
            'icon' => 'fa-envelope-o',
            'sort' => 1
        ]);

        $site->menus()->create([
            'site_id' => $site_id,
            'parent_id' => $parent->id,
            'name' => '短信日志',
            'url' => '/admin/sms/logs',
            'permission' => '@log',
            'icon' => 'fa-commenting-o',
            'sort' => 2
        ]);

        \Session::flash('flash_success', '添加成功');
        return true;
    }

    public static function findByDomain($domain)
    {
        return static::where('domain', $domain)
            ->orderBy('id')
            ->first();
    }
}
