<?php

namespace Modules\Activity\Web;

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
use Modules\Activity\Models\Activity;
use Request;
use Response;

/**
 * 活动
 */
class ActivityController extends BaseController
{
    protected $base_url = '/admin/activities';
    protected $view_path = 'activity.views';
    protected $module;

    public function __construct()
    {
        $this->module = Module::where('name', 'Activity')->first();
    }

    public function show(Domain $domain, $id)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $activity = Activity::with('data')->find($id);
        if (empty($activity)) {
            return abort(404);
        }
        $activity->incrementClick();

        return view($domain->theme->name . '.activities.detail', ['site' => $domain->site, 'activity' => $activity]);
    }

    public function slug(Domain $domain, $slug)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $activity = Activity::where('slug', $slug)
            ->first();
        if (empty($activity)) {
            return abort(404);
        }
        $activity->incrementClick();

        return view($domain->theme->name . '.activity.detail', ['site' => $domain->site, 'activity' => $activity]);
    }

    public function lists(Domain $domain)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $activities = Activity::where('state', Activity::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->get();

        return view($domain->theme->name . '.activity.index', ['site' => $domain->site, 'module' => $this->module, 'activities' => $activities]);
    }

    public function index()
    {
        if (Gate::denies('@activity')) {
            return abort(403);
        }

        $module = Module::transform($this->module->id);

        return view($this->view_path . '.index', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function create()
    {
        if (Gate::denies('@activity-create')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        return view('admin.contents.create', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function edit($id)
    {
        if (Gate::denies('@activity-edit')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        $activity = Activity::find($id);
        $activity->images = null;
        $activity->videos = null;
        $activity->audios = null;
        $activity->tags = $activity->tags()->pluck('name')->toArray();

        return view('admin.contents.edit', ['module' => $module, 'content' => $activity, 'base_url' => $this->base_url, 'back_url' => $this->base_url . '?category_id=' . $activity->category_id]);
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

        $activity = Activity::stores($input);

        event(new UserLogEvent(UserLog::ACTION_CREATE . '活动', $activity->id, $this->module->model_class));

        \Session::flash('flash_success', '添加成功');
        return redirect($this->base_url . '?category_id=' . $activity->category_id);
    }

    public function update($id)
    {
        $input = Request::all();

        $validator = Module::validate($this->module, $input);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $activity = Activity::updates($id, $input);

        event(new UserLogEvent(UserLog::ACTION_UPDATE . '活动', $activity->id, $this->module->model_class));

        \Session::flash('flash_success', '修改成功!');
        return redirect($this->base_url . '?category_id=' . $activity->category_id);
    }

    public function comments($refer_id)
    {
        $refer_type = $this->module->model_class;
        return view('admin.comments.list', compact('refer_id', 'refer_type'));
    }

    public function save($id)
    {
        $activity = Activity::find($id);

        if (empty($activity)) {
            return;
        }

        $activity->update(Request::all());
    }

    public function sort()
    {
        return Activity::sort();
    }

    public function top($id)
    {
        $activity = Activity::find($id);
        $activity->top = !$activity->top;
        $activity->save();
    }

    public function tag($id)
    {
        $tag = request('tag');
        $activity = Activity::find($id);
        if ($activity->tags()->were('name', $tag)->exists()) {
            $activity->tags()->where('name', $tag)->delete();
        } else {
            $activity->tags()->create([
                'site_id' => $activity->site_id,
                'name' => $tag,
                'sort' => strtotime(Carbon::now()),
            ]);
        }
    }

    public function state()
    {
        $input = request()->all();
        Activity::state($input);

        $ids = $input['ids'];
        $stateName = Activity::getStateName($input['state']);

        //记录日志
        foreach ($ids as $id) {
            event(new UserLogEvent('变更' . '活动' . UserLog::ACTION_STATE . ':' . $stateName, $id, $this->module->model_class));
        }

        //发布页面
        $site = auth()->user()->site;
        if ($input['state'] == Activity::STATE_PUBLISHED) {
            foreach ($ids as $id) {
                $this->dispatch(new PublishPage($site, $this->module, $id));
            }
        }
    }

    public function table()
    {
        return Activity::table();
    }

    public function categories()
    {
        return Response::json(Category::tree('', 0, $this->module->id, false));
    }
}
