<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModuleFieldRequest;
use App\Models\Module;
use App\Models\ModuleField;
use Request;
use Response;

class ModuleFieldController extends Controller
{
    public function __construct()
    {
    }

    public function show($id)
    {
        $module = Module::find($id);

        if (empty($module)) {
            \Session::flash('flash_warning', '无此记录');

            return redirect('/admin/modules');
        }

        $groups = string_to_option($module->groups);

        return view('admin.modules.fields.index', compact('module', 'groups'));
    }

    public function store(ModuleFieldRequest $request)
    {
        $input = $request->all();

        $ret = ModuleField::stores($input);
        if (!$ret) {
            redirect()->back()->withInput();
        }

        return redirect()->back();
    }

    public function edit($id)
    {
    }

    public function update($id, ModuleFieldRequest $request)
    {
        $input = $request->all();

        $count = ModuleField::where('name', $input['name'])
            ->where('module_id', $input['module_id'])
            ->where('id', '<>', $id)
            ->count();

        if($count > 0){
            \Session::flash('flash_warning', '字段已存在');
            return redirect()->back();
        }

        $ret = ModuleField::updates($id, $input);
        if (!$ret) {
            redirect()->back()->withInput();
        }

        \Session::flash('flash_success', '修改成功!');
        return redirect()->back();
    }

    public function save($id)
    {
        $field = ModuleField::find($id);

        if (empty($field)) {
            return;
        }

        $field->update(Request::all());
    }

    public function destroy($id)
    {
        $field = ModuleField::find($id);

        if ($field == null) {
            \Session::flash('flash_warning', '无此记录');
            return;
        }

        $field->delete();
        \Session::flash('flash_success', '删除成功');
    }

    public function table($module_id)
    {
        $module = Module::find($module_id);

        $fields = $module->fields()->orderBy('index')->get()->map(function ($field) {
            $attributes = $field->getAttributes();
            $attributes['type_name'] = $field->typeName();
            $attributes['editor_type_name'] = $field->editorTypeName();
            $attributes['column_align_name'] = $field->columnAlignName();
            return $attributes;
        });
        $ds = new \stdClass();
        $ds->data = $fields;

        return Response::json($ds);
    }
}

