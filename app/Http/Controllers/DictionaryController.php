<?php

namespace App\Http\Controllers;

use App\Http\Requests\DictionaryRequest;
use App\Models\Dictionary;
use Gate;
use Request;
use Response;

class DictionaryController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        if (Gate::denies('@dictionaries')) {
            $this->middleware('deny403');
        }

        $parent_id = Request::get('parent_id') ?: 0;

        return view('admin.dictionaries.index', compact('parent_id'));
    }

    public function create($parent_id)
    {
        return view('admin.dictionaries.create', compact('parent_id'));
    }

    public function store(DictionaryRequest $request)
    {
        $input = $request->all();

        $sort = Dictionary::where('parent_id', '=', $input['parent_id'])->max('sort');
        $sort += 1;

        $input['sort'] = $sort;
        $input['site_id'] = \Auth::user()->site_id;

        Dictionary::create($input);

        \Session::flash('flash_success', '添加成功');
        return redirect('/admin/dictionaries?parent_id=' . $input['parent_id']);
    }


    public function edit($id)
    {
        $dictionary = Dictionary::find($id);

        if (empty($dictionary)) {
            \Session::flash('flash_warning', '无此记录');

            return redirect('/admin/dictionaries');
        }

        return view('admin.dictionaries.edit', compact('dictionary'));
    }

    public function update($id, DictionaryRequest $request)
    {
        $input = $request->all();

        $dictionary = Dictionary::find($id);

        if ($dictionary == null) {
            \Session::flash('flash_warning', '无此记录');
            redirect()->back()->withInput();
        }
        $dictionary->update($input);

        \Session::flash('flash_success', '修改成功!');
        return redirect('/admin/dictionaries?parent_id=' . $input['parent_id']);
    }

    public function save($id)
    {
        $dictionary = Dictionary::find($id);

        if ($dictionary == null) {
            return;
        }

        $dictionary->update(Request::all());
    }

    public function destroy($id)
    {
        $dictionary = Dictionary::find($id);
        $dictionary->delete();
        \Session::flash('flash_success', '删除成功');
    }

    public function tree()
    {
        return Response::json(Dictionary::tree('', 0));
    }

    public function table($parent_id)
    {
        $dictionaries = Dictionary::owns()
            ->where('parent_id', $parent_id)
            ->orderBy('sort')
            ->get();

        $dictionaries->transform(function ($dictionary) {
            return [
                'id' => $dictionary->id,
                'site_name' => $dictionary->site->title,
                'parent_id' => $dictionary->parent_id,
                'code' => $dictionary->code,
                'name' => $dictionary->name,
                'value' => $dictionary->value,
                'sort' => $dictionary->sort,
            ];
        });

        $ds = new \stdClass();
        $ds->data = $dictionaries;

        return Response::json($ds);
    }

}
