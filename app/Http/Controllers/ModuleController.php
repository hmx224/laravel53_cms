<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModuleRequest;
use App\Models\Module;
use Gate;
use Request;

class ModuleController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        if (Gate::denies('@module')) {
            $this->middleware('deny403');
        }

        //获取当前模型ID
        $module_id = Request::get('module_id') ?: 0;

        return view('admin.modules.index', compact('module_id'));
    }

    public function create()
    {
        return view('admin.modules.create');
    }

    public function store(ModuleRequest $request)
    {
        $input = $request->all();

        $ret = Module::stores($input);
        if (!$ret) {
            redirect()->back()->withInput();
        }

        return redirect('/admin/modules');
    }

    public function copy(ModuleRequest $request)
    {
        $input = $request->all();

        $ret = Module::copy($input);
        if (!$ret) {
            redirect()->back()->withInput();
        }

        return redirect('/admin/modules');
    }

    public function edit($id)
    {
        $module = Module::find($id);

        if (empty($module)) {
            \Session::flash('flash_warning', '无此记录');

            return redirect('/admin/modules');
        }

        return view('admin.modules.edit', compact('module'));
    }


    public function update($id, ModuleRequest $request)
    {
        $input = $request->all();

        $ret = Module::updates($id, $input);
        if (!$ret) {
            redirect()->back()->withInput();
        }

        \Session::flash('flash_success', '修改成功!');
        return redirect('/admin/modules');
    }

    public function save($id)
    {
        $module = Module::find($id);

        if (empty($module)) {
            return;
        }

        $module->update(Request::all());
    }

    public function destroy($id)
    {
        $category = Module::find($id);

        if ($category == null) {
            \Session::flash('flash_warning', '无此记录');
            return;
        }

        $child = Module::where('parent_id', $id)
            ->first();
        if (!empty($child)) {
            \Session::flash('flash_warning', '此栏目有子栏目,不允许删除该栏目');
            return;
        }

        $content = Content::where('category_id', $id)
            ->first();
        if (!empty($content)) {
            \Session::flash('flash_warning', '此栏目已有内容,不允许删除该栏目');
            return;
        }

        $category->delete();
        \Session::flash('flash_success', '删除成功');
    }

    public function table()
    {
        return Module::table();
    }

    public function migrate($id)
    {
        $module = Module::find($id);
        if (empty($module)) {
            \Session::flash('flash_warning', '无此记录');
            return redirect()->back();
        }
        if ($module->is_lock) {
            \Session::flash('flash_warning', '此模块已锁定');
            return redirect()->back();
        }

        Module::migrate($module);

        \Session::flash('flash_success', '生成数据结构成功!');
        return redirect()->back();
    }

    public function generate($id)
    {
        $module = Module::find($id);
        if (empty($module)) {
            \Session::flash('flash_warning', '无此记录');
            return redirect()->back();
        }
        if ($module->is_lock) {
            \Session::flash('flash_warning', '此模块已锁定');
            return redirect()->back();
        }

        Module::generate($module);

        \Session::flash('flash_success', '生成模块代码成功!');
        return redirect()->back();
    }
}

