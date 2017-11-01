<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Jobs\PublishCategory;
use App\Models\Category;
use App\Models\Module;
use DB;
use Gate;
use Request;
use Response;

class CategoryController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        if (Gate::denies('@category')) {
            $this->middleware('deny403');
        }

        return view('admin.categories.index');
    }

    public function create($category_id)
    {
        $modules = Module::with('fields')
            ->where('state', Module::STATE_ENABLE)
            ->whereHas('fields', function ($query) {
                $query->where('name', 'category_id');
            })
            ->pluck('title', 'id')
            ->toArray();

        return view('admin.categories.create', compact('category_id', 'modules'));
    }

    public function store(CategoryRequest $request)
    {
        $input = Request::all();
        $category_id = $input['category_id'];

        $sort = Category::select(DB::raw('max(sort) as max'))
            ->where('parent_id', '=', $category_id)
            ->first()->max;

        $sort += 1;

        $input['sort'] = $sort;
        $input['parent_id'] = $category_id;
        $input['site_id'] = \Auth::user()->site_id;
        $input['state'] = Category::STATE_ENABLED;

        Category::create($input);

        $url = '/admin/categories?category_id=' . $category_id;

        \Session::flash('flash_success', '添加成功');
        return redirect($url);
    }

    public function edit($id)
    {
        $category = Category::find($id);

        if (empty($category)) {
            \Session::flash('flash_warning', '无此记录');

            return redirect('/admin/categories');
        }
        $modules = Module::with('fields')
            ->where('state', Module::STATE_ENABLE)
            ->whereHas('fields', function ($query) {
                $query->where('name', 'category_id');
            })
            ->pluck('title', 'id')
            ->toArray();

        return view('admin.categories.edit', compact('category', 'modules'));
    }


    public function update($id, CategoryRequest $request)
    {
        $category = Category::find($id);

        if ($category == null) {
            \Session::flash('flash_warning', '无此记录');
            return redirect()->to($this->getRedirectUrl())
                ->withInput($request->input());
        }

        $input = Request::all();

        $category->update($input);

        $category_id = $category->parent_id > 0 ? $category->parent_id : $category->id;

        \Session::flash('flash_success', '修改成功!');

        $url = '/admin/categories?category_id=' . $category_id;

        return redirect($url);
    }

    public function save($id)
    {
        $category = Category::find($id);

        if (empty($category)) {
            return;
        }

        $category->update(Request::all());
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if ($category == null) {
            \Session::flash('flash_warning', '无此记录');
            return;
        }

        if ($category->children()->count() > 0) {
            \Session::flash('flash_warning', '此栏目有子栏目,不允许删除该栏目');
            return;
        }

        $query = call_user_func([$category->module->model_class, 'where'], 'category_id', $id);
        if ($query->count() > 0) {
            \Session::flash('flash_warning', '此栏目已有内容,不允许删除该栏目');
            return;
        }

        $category->delete();
        \Session::flash('flash_success', '删除成功');
    }

    public function lists($category_id)
    {
        $category = Category::find($category_id);
        if (empty($category)) {
            abort(404);
        }
        return view("admin.templates.categories.list", compact('category'));
    }

    public function tree()
    {
        return Response::json(Category::tree('', Category::ID_ROOT));
    }

    public function table()
    {
        $category_id = Request::get('category_id');

        $categories = Category::owns()
            ->where('parent_id', $category_id)
            ->orderBy('sort')
            ->get();

        $categories->transform(function ($category) {
            return [
                'id' => $category->id,
                'code' => $category->code,
                'name' => $category->name,
                'module_title' => $category->module->title,
                'likes' => $category->likes,
                'parent_id' => $category->parent_id,
                'slug' => $category->slug,
                'desc' => $category->description,
                'state_name' => $category->stateName(),
                'sort' => $category->sort,
            ];
        });

        $ds = new \stdClass();
        $ds->data = $categories;

        return Response::json($ds);
    }

    public function publish($id)
    {
        $category = Category::find($id);
        if (empty($category)) {
            \Session::flash('flash_warning', '无此记录');
            return redirect()->back();
        }

        $this->dispatch(new PublishCategory($category));

        \Session::flash('flash_success', '已添加到发布队列');
        return redirect()->back();
    }
}

