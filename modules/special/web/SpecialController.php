<?php

namespace Modules\Special\Web;

use App\Events\UserLogEvent;
use App\Http\Controllers\BaseController;
use App\Jobs\PublishPage;
use App\Models\Category;
use App\Models\Domain;
use App\Models\Module;
use App\Models\UserLog;
use Auth;
use Carbon\Carbon;
use Gate;
use Modules\Special\Models\Special;
use Request;
use Response;

/**
 * 专题
 */
class SpecialController extends BaseController
{
    protected $base_url = '/admin/specials';
    protected $view_path = 'special.views';
    protected $module;

    public function __construct()
    {
        $this->module = Module::where('name', 'Special')->first();
    }

    public function show(Domain $domain, $id)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $special = Special::find($id);
        if (empty($special)) {
            return abort(404);
        }
        $special->incrementClick();

        return view($domain->theme->name . '.specials.detail', ['site' => $domain->site, 'special' => $special]);
    }

    public function slug(Domain $domain, $slug)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $special = Special::where('slug', $slug)
            ->first();
        if (empty($special)) {
            return abort(404);
        }
        $special->incrementClick();

        return view($domain->theme->name . '.specials.detail', ['site' => $domain->site, 'special' => $special]);
    }

    public function lists(Domain $domain)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $specials = Special::where('state', Special::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->get();

        return view($domain->theme->name . '.specials.index', ['site' => $domain->site, 'module' => $this->module, 'specials' => $specials]);
    }

    public function index()
    {
        if (Gate::denies('@special')) {
            return abort(403);
        }

        $module = Module::transform($this->module->id);

        return view($this->view_path . '.index', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function create()
    {
        if (Gate::denies('@special-create')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        return view('admin.contents.create', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function edit($id)
    {
        if (Gate::denies('@special-edit')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        $special = Special::find($id);
        $special->images = null;
        $special->videos = null;
        $special->audios = null;
        $special->tags = $special->tags()->pluck('name')->toArray();

        return view('admin.contents.edit', ['module' => $module, 'content' => $special, 'base_url' => $this->base_url, 'back_url' => $this->base_url . '?category_id=' . $special->category_id]);
    }

    public function store()
    {
        $input = Request::all();
        $input['site_id'] = Auth::user()->site_id;
        $input['user_id'] = Auth::user()->id;

        $validator = Module::validate($this->module, $input);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $special = Special::stores($input);

        event(new UserLogEvent(UserLog::ACTION_CREATE . '专题', $special->id, $this->module->model_class));

        \Session::flash('flash_success', '添加成功');
        return redirect($this->base_url . '?category_id=' . $special->category_id);
    }

    public function update($id)
    {
        $input = Request::all();

        $validator = Module::validate($this->module, $input);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $special = Special::updates($id, $input);

        event(new UserLogEvent(UserLog::ACTION_UPDATE . '专题', $special->id, $this->module->model_class));

        \Session::flash('flash_success', '修改成功!');
        return redirect($this->base_url . '?category_id=' . $special->category_id);
    }

    public function comments($refer_id)
    {
        $refer_type = $this->module->model_class;
        return view('admin.comments.list', compact('refer_id', 'refer_type'));
    }

    public function save($id)
    {
        $special = Special::find($id);

        if (empty($special)) {
            return;
        }

        $special->update(Request::all());
    }

    public function sort()
    {
        return Special::sort();
    }

    public function top($id)
    {
        $special = Special::find($id);
        $special->top = !$special->top;
        $special->save();
    }

    public function tag($id)
    {
        $tag = request('tag');
        $special = Special::find($id);
        if ($special->tags()->where('name', $tag)->exists()) {
            $special->tags()->where('name', $tag)->delete();
        } else {
            $special->tags()->create([
                'site_id' => $special->site_id,
                'name' => $tag,
                'sort' => strtotime(Carbon::now()),
            ]);
        }
    }

    public function state()
    {
        $input = request()->all();
        Special::state($input);

        $ids = $input['ids'];
        $stateName = Special::getStateName($input['state']);

        //记录日志
        foreach ($ids as $id) {
            event(new UserLogEvent('变更' . '专题' . UserLog::ACTION_STATE . ':' . $stateName, $id, $this->module->model_class));
        }

        //发布页面
        $site = auth()->user()->site;
        if ($input['state'] == Special::STATE_PUBLISHED) {
            foreach ($ids as $id) {
                $this->dispatch(new PublishPage($site, $this->module, $id));
            }
        }
    }

    public function table()
    {
        return Special::table();
    }

    public function categories()
    {
        return Response::json(Special::tree('', 0, $this->module->id, true));
    }
}
