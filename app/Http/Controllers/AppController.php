<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppRequest;
use App\Models\App;
use Request;
use Response;
use Gate;
use Auth;

class AppController extends Controller
{

    public function __construct()
    {
    }

    public function index()
    {
        if (Gate::denies('@app')) {
            $this->middleware('deny403');
        }

        return view('admin.apps.index');
    }

    public function create()
    {
        return view('admin.apps.create');
    }

    public function store(AppRequest $request)
    {
        $input = Request::all();
        $input['username'] = Auth::user()->name;
        App::create($input);
        \Session::flash('flash_success', '添加成功');
        return redirect('/admin/apps');
    }

    public function edit($id)
    {
        $apps = App::find($id);
        if ($apps == null) {
            \Session::flash('flash_warning', '无此记录');
            return redirect('/admin/apps');
        }

        return view('admin.apps.edit', compact('apps'));
    }

    public function update($id, AppRequest $request)
    {
        $apps = App::find($id);

        if ($apps == null) {
            \Session::flash('flash_warning', '无此记录');
            return redirect()->to($this->getRedirectUrl())
                ->withInput($request->input());
        }
        $input = Request::all();
        $input['username'] = Auth::user()->name;
        $apps->update($input);

        \Session::flash('flash_success', '修改成功!');
        return redirect('/admin/apps');
    }

    public function destroy($id)
    {
        $app = App::find($id);
        if ($app == null) {
            \Session::flash('flash_warning', '无此记录');
            return;
        }
        $app->delete();
        \Session::flash('flash_success', '删除成功');
    }

    public function table()
    {
        $apps = App::all();

        $apps->transform(function ($app) {
            return [
                'id' => $app->id,
                'name' => $app->name,
                'android_version' => $app->android_version,
                'android_force' => $app->android_force,
                'android_url' => $app->android_url,
                'ios_version' => $app->ios_version,
                'ios_force' => $app->ios_force,
                'ios_url' => $app->ios_url,
                'logo_url' => $app->logo_url,
                'username' => $app->username,
                'created_at' => $app->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $app->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        $ds = new \stdClass();
        $ds->data = $apps;

        return Response::json($ds);
    }

}