<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Domain;
use App\Models\Module;

class WebController extends Controller
{
    public function list1(Domain $domain, $module, $path1, $template, $page = 1)
    {
        return $this->list($domain, $module, $path1, $template, $page);
    }

    public function list2(Domain $domain, $module, $path1, $path2, $template, $page = 1)
    {
        return $this->list($domain, $module, "$path1/$path2", $template, $page);
    }

    public function list3(Domain $domain, $module, $path1, $path2, $path3, $template, $page = 1)
    {
        return $this->list($domain, $module, "$path1/$path2/$path3", $template, $page);
    }

    public function list4(Domain $domain, $module, $path1, $path2, $path3, $path4, $template, $page = 1)
    {
        return $this->list($domain, $module, "$path1/$path2/$path3/$path4", $template, $page);
    }

    public function list(Domain $domain, $module_path, $category_path, $template, $page)
    {
        //搜索模块
        $module = Module::findByPath($module_path);
        if (empty($module)) {
            return abort(404);
        }

        //搜索栏目
        $category = Category::findByFullPath($category_path);
        if (empty($category)) {
            return abort(404);
        }

        //从配置中获取分页大小
        $limit = config("theme.default.categories.$category->id.$template.page_size");
        if (empty($limit)) {
            $limit = config("theme.default.page_size", 30);
        }
        $offset = $limit * ($page - 1);

        //查询数据
        $items = $category->module->model_class::where('site_id', $domain->site->id)
            ->orderBy('sort', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();
        if (count($items) == 0) {
            return abort(404);
        }

        $path = str_replace('/', '.', $category->full_path);

        //返回视图内容
        return view("default.$module->path.$path.$template", [
            'module' => $module,
            'category' => $category,
            $category->module->plural => $items,
        ]);
    }

    public function detail(Domain $domain, $module, $date, $id)
    {
        //搜索模块
        $module = Module::findByPath($module);
        if (empty($module)) {
            return abort(404);
        }

        $item = $module->model_class::find($id);
        if (empty($item)) {
            return abort(404);
        }

        $path = str_replace('/', '.', $item->category->full_path);
        $theme = $domain->theme->name;

        return view("$theme.$module->path.$path.detail", [
            'module' => $module,
            $module->singular => $item
        ]);
    }
}
