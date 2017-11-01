<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuRequest;
use App\Models\Menu;
use App\Models\Module;
use Gate;
use Request;
use Response;

class MenuController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        if (Gate::denies('@menu')) {
            $this->middleware('deny403');
        }

        $menus = auth()->user()->site->menus()->where('parent_id', 0)->orderBy('sort')->get();

        return view('admin.menus.index', compact('menus'));
    }

    public function store(MenuRequest $request)
    {
        $input = $request->all();
        $input['site_id'] = auth()->user()->site_id;
        $input['sort'] = auth()->user()->site->menus()->count();

        Menu::create($input);

        return redirect('/admin/menus');
    }

    public function update($id, MenuRequest $request)
    {
        $input = $request->all();

        $menu = Menu::find($id);
        if ($menu == null) {
            \Session::flash('flash_warning', '无此记录');
            redirect()->back()->withInput();
        }

        $menu->update($input);

        \Session::flash('flash_success', '修改成功!');
        return redirect('/admin/menus');
    }

    public function destroy($id)
    {
        $menu = Menu::find($id);

        if ($menu == null) {
            \Session::flash('flash_warning', '无此记录');
            return;
        }

        $menu->children()->delete();
        $menu->delete();

        \Session::flash('flash_success', '删除成功');
    }

    public function modules()
    {
        $modules = Module::where('state', Module::STATE_ENABLE)
            ->get();
        $nodes = [];
        foreach ($modules as $module) {
            $nodes[] = (object)[
                'id' => $module->id,
                'text' => $module->title,
                'icon' => 'fa ' . $module->icon,
                'url' => '/admin/' . $module->path,
                'permission' => '@' . strtolower($module->name),
                'fa_icon' => $module->icon,
            ];
        }
        return Response::json($nodes);
    }

    public function sort()
    {
        $menus = Request::get('data');

        $menus = json_decode(json_encode($menus));

        Menu::sort($menus);
    }
}

