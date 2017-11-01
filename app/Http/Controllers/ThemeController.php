<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeRequest;
use App\Models\Module;
use App\Models\Theme;
use Dropbox\Exception;
use Response;

class ThemeController extends BaseController
{
    public function __construct()
    {
    }

    public function index()
    {
        $snippets = config('snippet');
        return view('admin.themes.index', compact('snippets'));
    }

    public function store(ThemeRequest $request)
    {
        $input = $request->all();

        Theme::create($input);

        return redirect('/admin/themes');
    }

    public function update($id, ThemeRequest $request)
    {
        $input = $request->all();

        $theme = Theme::find($id);
        if ($theme == null) {
            \Session::flash('flash_warning', '无此记录');
            redirect()->back()->withInput();
        }

        $theme->update($input);

        \Session::flash('flash_success', '修改成功!');
        return redirect('/admin/themes');
    }

    public function destroy($id)
    {
        $theme = Theme::find($id);

        if ($theme == null) {
            \Session::flash('flash_warning', '无此记录');
            return;
        }

        $theme->delete();

        \Session::flash('flash_success', '删除成功');
    }

    public function module($module_id)
    {
        $module = Module::find($module_id);
        $module['fields'] = $module->fields()->orderBy('index')->get();

        return $this->responseSuccess(
            $module
        );
    }

    public function getPathNodes($type = 'asset', $path, $extension, $array = [], $module_id = null)
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
                'nodes' => $this->getPathNodes($type, $path . DIRECTORY_SEPARATOR . $dir, $extension, [], $module_id),
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
            } else if ($file == 'category.blade.php') {
                $tag = '栏目页';
            } else if ($file == 'detail.blade.php') {
                $tag = '详情页';
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

    public function getThemeNodes($theme)
    {
        $nodes = [
            [
                'text' => 'css',
                'color' => '#00a47a',
                'tags' => ['样式'],
                'extension' => '.css',
                'path' => $theme->name . '/css',
                'nodes' => $this->getPathNodes('asset', $theme->name . DIRECTORY_SEPARATOR . 'css', '.css'),
            ],
            [
                'text' => 'js',
                'color' => '#f60',
                'tags' => ['脚本'],
                'extension' => '.js',
                'path' => $theme->name . '/js',
                'nodes' => $this->getPathNodes('asset', $theme->name . DIRECTORY_SEPARATOR . 'js', '.js'),
            ],
            [
                'text' => 'layout',
                'color' => '#08c',
                'tags' => ['布局'],
                'extension' => '.blade.php',
                'path' => $theme->name . '/layouts',
                'nodes' => $this->getPathNodes('views', $theme->name . DIRECTORY_SEPARATOR . 'layouts', '.blade.php'),
            ]
        ];

        $modules = Module::all();
        foreach ($modules as $module) {
            $nodes[] = [
                'id' => $module->id,
                'type' => 'module',
                'text' => $module->path,
                'tags' => [$module->title],
                'extension' => '.blade.php',
                'path' => $theme->name . DIRECTORY_SEPARATOR . $module->path,
                'nodes' => $this->getPathNodes('view', $theme->name . DIRECTORY_SEPARATOR . $module->path, '.blade.php', [], $module->id),
            ];
        }

        $nodes = array_merge($nodes, $this->getPathNodes('root', $theme->name, '.blade.php', $nodes));

        return $nodes;
    }

    public function tree()
    {
        $themes = Theme::orderBy('name')
            ->get();

        $nodes = [];
        foreach ($themes as $theme) {
            $node = [
                'id' => $theme->id,
                'type' => 'theme',
                'text' => $theme->name,
                'tags' => [$theme->title],
                'extension' => '.blade.php',
                'path' => $theme->name,
                'nodes' => Theme::getNodes($theme)
            ];
            $nodes[] = $node;
        }

        return Response::json($nodes);
    }

    public function readFile()
    {
        $path = request('path');

        $extension = strtolower(pathinfo($path)['extension']);
        if ($extension == 'php') {
            $path = theme_view_path($path);
        } else {
            $path = theme_asset_path($path);
        }

        //判断文件是否存在
        if (file_exists($path)) {
            return $this->responseSuccess(file_get_contents($path));
        } else {
            return $this->responseError('此文件文件不存在');
        }
    }

    public function writeFile()
    {
        $path = request('path');
        $data = request('data');

        $extension = strtolower(pathinfo($path)['extension']);
        if ($extension == 'php') {
            $path = theme_view_path($path);
        } else {
            $path = theme_asset_path($path);
        }

        //判断文件是否存在
        if (!file_exists($path)) {
            return $this->responseError('此文件文件不存在');
        }

        try {
            file_put_contents($path, $data);
            return $this->responseSuccess();
        } catch (Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    public function createFile()
    {
        $path = request('path');
        $name = request('name');
        $extension = strtolower(request('extension'));

        //安全性检查
        if (str_contains($path, '.') || str_contains($name, '.')) {
            \Session::flash('flash_warning', '路径错误');
            return redirect()->back();
        }
        if (!in_array($extension, ['.js', '.css', '.blade.php'])) {
            \Session::flash('flash_warning', '扩展名错误');
            return redirect()->back();
        }

        //禁止上传.php文件到public目录
        if (str_contains($extension, '.php')) {
            $path = theme_view_path($path);
        } else {
            $path = theme_asset_path($path);
        }

        try {
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }

            $filename = $path . $name . $extension;

            file_put_contents($filename, '//' . $name . $extension);

            \Session::flash('flash_success', '创建成功!');
            return redirect('/admin/themes');
        } catch (Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    public function removeFile()
    {
        $path = request('path');

        $extension = strtolower(pathinfo($path)['extension']);
        if ($extension == 'php') {
            $path = theme_view_path($path);
        } else {
            $path = theme_asset_path($path);
        }

        //判断文件是否存在
        if (!file_exists($path)) {
            return $this->responseError('此文件文件不存在');
        }

        try {
            unlink($path);

            \Session::flash('flash_success', '删除成功!');
            return $this->responseSuccess();
        } catch (Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
