<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiteRequest;
use App\Jobs\PublishSite;
use App\Models\Site;
use App\Models\Theme;
use Auth;
use Gate;
use Request;
use Response;

class SiteController extends BaseController
{
    public function __construct()
    {
    }

    public function index()
    {
        if (Gate::denies('@site')) {
            $this->middleware('deny403');
        }

        return view('admin.sites.index');
    }

    public function create()
    {
        $themes = Theme::pluck('title', 'id')->toArray();
        return view('admin.sites.create', compact('themes'));
    }

    public function edit($id)
    {
        $site = Site::find($id);

        if ($site == null) {

            \Session::flash('flash_warning', '无此记录');
            return redirect('/admin/sites');
        }

        $themes = Theme::pluck('title', 'id')->toArray();

        return view('admin.sites.edit', compact('site', 'themes'));
    }

    public function update($id, SiteRequest $request)
    {
        $sites = Site::find($id);

        if ($sites == null) {
            \Session::flash('flash_warning', '无此记录');
            return redirect()->to($this->getRedirectUrl())
                ->withInput($request->input());
        }
        $input = Request::all();
        $input['user_id'] = Auth::user()->id;
        $sites->update($input);

        \Session::flash('flash_success', '修改成功!');
        return redirect('/admin/sites');
    }

    public function destroy($id)
    {
        $site = Site::find($id);
        if ($site == null) {
            \Session::flash('flash_warning', '无此记录');
            return;
        }
        $site->delete();
        \Session::flash('flash_success', '删除成功');
    }

    public function store(SiteRequest $request)
    {
        $input = Request::all();
        $site = Site::stores($input);

        if (!$site) {
            redirect()->back()->withInput();
        }

        return redirect('/admin/sites');
    }

    public function table()
    {
        $sites = Site::all();

        $sites->transform(function ($site) {
            return [
                'id' => $site->id,
                'name' => $site->name,
                'title' => $site->title,
                'directory' => $site->directory,
                'domain' => $site->domain,
                'user_name' => $site->user->name,
                'updated_at' => $site->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        $ds = new \stdClass();
        $ds->data = $sites;

        return Response::json($ds);
    }

    public function publish($id)
    {
        $site = Site::find($id);
        if (empty($site)) {
            \Session::flash('flash_warning', '无此记录');
            return redirect()->back();
        }

        $this->dispatch(new PublishSite($site));

        \Session::flash('flash_success', '已添加到发布队列');
        return redirect()->back();
    }
}
