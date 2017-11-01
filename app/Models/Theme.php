<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'title',
    ];

    public static function getPathNodes($type = 'asset', $path, $extension, $array = [], $module_id = null)
    {
        if ($type == 'asset') {
            $fullPath = theme_asset_path($path);
        } else {
            $fullPath = theme_view_path($path);
        }

        if (!is_dir($fullPath)) {
            return [];
        }

        $dir = dir($fullPath);
        $dirs = [];
        $files = [];
        while ($file = $dir->read()) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            //判断当前元素是否已存在
            $exist = array_first($array, function ($node) use ($file) {
                return $node['text'] == $file;
            });
            if (!empty($exist)) {
                continue;
            }

            if (is_file($fullPath . DIRECTORY_SEPARATOR . $file)) {//当前为文件
                $files[] = $file;
            } else {//当前为目录
                $dirs[] = $file;
            }
        }

        $nodes = [];

        //目录
        foreach ($dirs as $dir) {
            $nodes[] = [
                'text' => $dir,
                'extension' => $extension,
                'path' => $path . DIRECTORY_SEPARATOR . $dir,
                'nodes' => static::getPathNodes($type, $path . DIRECTORY_SEPARATOR . $dir, $extension, [], $module_id),
            ];
        }

        //文件
        foreach ($files as $file) {
            $tag = null;
            if ($file == 'index.blade.php') {
                if ($type == 'root') {
                    $tag = '首页';
                } else {
                    $tag = '列表页';
                }
            } else if ($file == 'detail.blade.php') {
                $tag = '详情页';
            } else {
                $tag = '附加页';
            }
            $nodes[] = [
                'text' => $file,
                'tags' => [$tag],
                'icon' => 'fa fa-file-o',
                'path' => $path . DIRECTORY_SEPARATOR . $file,
                'module_id' => $module_id,
            ];
        }

        return $nodes;
    }

    public static function getNodes($theme)
    {
        $nodes = [
            [
                'text' => 'css',
                'color' => '#00a47a',
                'tags' => ['样式'],
                'extension' => '.css',
                'path' => $theme->name . '/css',
                'nodes' => static::getPathNodes('asset', $theme->name . DIRECTORY_SEPARATOR . 'css', '.css'),
            ],
            [
                'text' => 'js',
                'color' => '#f60',
                'tags' => ['脚本'],
                'extension' => '.js',
                'path' => $theme->name . '/js',
                'nodes' => static::getPathNodes('asset', $theme->name . DIRECTORY_SEPARATOR . 'js', '.js'),
            ],
            [
                'text' => 'layout',
                'color' => '#08c',
                'tags' => ['布局'],
                'extension' => '.blade.php',
                'path' => $theme->name . '/layouts',
                'nodes' => static::getPathNodes('views', $theme->name . DIRECTORY_SEPARATOR . 'layouts', '.blade.php'),
            ]
        ];

        $modules = Module::all();
        foreach ($modules as $module) {
            $children = static::getCategoryNodes($theme, $module, 0);
            $children = array_merge($children, static::getPathNodes('views', $theme->name . DIRECTORY_SEPARATOR . $module->path, '.blade.php', $children, $module->id));

            $nodes[] = [
                'id' => $module->id,
                'type' => 'module',
                'text' => $module->path,
                'tags' => [$module->title],
                'extension' => '.blade.php',
                'path' => $theme->name . DIRECTORY_SEPARATOR . $module->path,
                'nodes' => $children,
            ];
        }

        $nodes = array_merge($nodes, static::getPathNodes('views', $theme->name, '.blade.php', $nodes));

        return $nodes;
    }

    public static function getCategoryNodes($theme, $module, $parent_id)
    {
        $nodes = [];

        $categories = Category::where('parent_id', $parent_id)
            ->where('module_id', $module->id)
            ->orderBy('sort')
            ->get();
        foreach ($categories as $category) {

            $children = static::getCategoryNodes($theme, $module, $category->id);
            $children = array_merge($children, static::getPathNodes('views', $theme->name . DIRECTORY_SEPARATOR . $module->path . DIRECTORY_SEPARATOR . $category->full_path, '.blade.php', $children, $module->id));

            $nodes[] = [
                'id' => $category->id,
                'type' => 'module',
                'text' => $category->code,
                'tags' => [$category->name],
                'extension' => '.blade.php',
                'path' => $theme->name . DIRECTORY_SEPARATOR . str_replace('/', '.', $category->full_path),
                'nodes' => $children,
            ];
        }

        return $nodes;
    }
}
