<?php

namespace Modules\Video\Web;

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
use Modules\Video\Models\Video;
use Request;
use Response;

/**
 * 媒资
 */
class VideoController extends BaseController
{
    protected $base_url = '/admin/videos';
    protected $view_path = 'video.views';
    protected $module;
    protected $video;

    public function __construct(Video $video)
    {
        $this->module = Module::where('name', 'Video')->first();
        $this->video = $video;
    }

    public function show(Domain $domain, $id)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $video = Video::find($id);
        if (empty($video)) {
            return abort(404);
        }
        $video->incrementClick();

        return view($domain->theme->name . '.videos.detail', ['site' => $domain->site, 'video' => $video]);
    }

    public function slug(Domain $domain, $slug)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $video = Video::where('slug', $slug)
            ->first();
        if (empty($video)) {
            return abort(404);
        }
        $video->incrementClick();

        return view($domain->theme->name . '.videos.detail', ['site' => $domain->site, 'video' => $video]);
    }

    public function lists(Domain $domain)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $videos = Video::where('state', Video::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->get();

        return view($domain->theme->name . '.videos.index', ['site' => $domain->site, 'module' => $this->module, 'videos' => $videos]);
    }

    public function category(Domain $domain, $category_id)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $category = Category::find($category_id);
        if (empty($category)) {
            return abort(404);
        }

        $videos = Video::where('category_id', $category_id)
            ->where('state', Video::STATE_PUBLISHED)
            ->orderBy('top', 'desc')
            ->orderBy('sort', 'desc')
            ->get();

        return view($domain->theme->name . '.videos.category', ['site' => $domain->site, 'category' => $category, 'videos' => $videos]);
    }

    public function index()
    {
        if (Gate::denies('@video')) {
            return abort(403);
        }

        $page = Video::PAGE_NUM;

        $state = Video::DEFAULT_STATE;

        $videos = $this->video->getVideoList($page, $state, []);

        $total = $videos->total();

        $module = Module::transform($this->module->id);

        return view($this->view_path . '.index', ['module' => $module, 'base_url' => $this->base_url, 'videos' => $videos, 'state' => $state, 'total' => $total]);

    }

    public function list()
    {
        if (Gate::denies('@video')) {
            return abort(403);
        }

        $module = Module::transform($this->module->id);

        $state = Video::DEFAULT_STATE_LIST;

        return view($this->view_path . '.list', ['module' => $module, 'base_url' => $this->base_url, 'state' => $state,]);
    }

    public function create()
    {
        if (Gate::denies('@video-create')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        return view('admin.contents.create', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function edit($id)
    {
        if (Gate::denies('@video-edit')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        $video = Video::find($id);
        $video->images = null;
        $video->videos = null;
        $video->audios = null;
        $video->tags = $video->tags()->pluck('name')->toArray();

        return view('admin.contents.edit', ['module' => $module, 'content' => $video, 'base_url' => $this->base_url, 'back_url' => $this->base_url . '?category_id=' . $video->category_id]);
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

        $video = Video::stores($input);

        event(new UserLogEvent(UserLog::ACTION_CREATE . '媒资', $video->id, $this->module->model_class));

        \Session::flash('flash_success', '添加成功');
        return redirect($this->base_url . '?category_id=' . $video->category_id);
    }

    public function update($id)
    {
        $input = Request::all();

        $validator = Module::validate($this->module, $input);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $video = Video::updates($id, $input);

        event(new UserLogEvent(UserLog::ACTION_UPDATE . '媒资', $video->id, $this->module->model_class));

        \Session::flash('flash_success', '修改成功!');
        return redirect($this->base_url . '?category_id=' . $video->category_id);
    }

    public function comments($refer_id)
    {
        $refer_type = $this->module->model_class;
        return view('admin.comments.list', compact('refer_id', 'refer_type'));
    }

    public function save($id)
    {
        $video = Video::find($id);

        if (empty($video)) {
            return;
        }

        $video->update(Request::all());
    }

    public function sort()
    {
        return Video::sort();
    }

    public function top($id)
    {
        $video = Video::find($id);
        $video->top = !$video->top;
        $video->save();
    }

    public function tag($id)
    {
        $tag = request('tag');
        $video = Video::find($id);
        if ($video->tags()->where('name', $tag)->exists()) {
            $video->tags()->where('name', $tag)->delete();
        } else {
            $video->tags()->create([
                'site_id' => $video->site_id,
                'name' => $tag,
                'sort' => strtotime(Carbon::now()),
            ]);
        }
    }

    public function state()
    {
        $input = request()->all();

        Video::state($input);

        $ids = $input['ids'];
        $stateName = Video::getStateName($input['state']);

        //记录日志
        foreach ($ids as $id) {
            event(new UserLogEvent('变更' . '媒资' . UserLog::ACTION_STATE . ':' . $stateName, $id, $this->module->model_class));
        }

        //发布页面
        $site = auth()->user()->site;
        if ($input['state'] == Video::STATE_PUBLISHED) {
            foreach ($ids as $id) {
                $this->dispatch(new PublishPage($site, $this->module, $id));
            }
        }
    }

    public function table()
    {
        return Video::table();
    }

    public function categories()
    {
        return Response::json(Category::tree('', 0, $this->module->id, false));
    }

    //筛选按钮
    public function filters($state)
    {
        $page = Video::PAGE_NUM;
        if (!is_numeric($state)) {
            $filters = json_decode($state, true);
            $state = $filters['state'];
            $videos = $this->video->getVideoList($page, $state, $filters);
        } else {
            $videos = $this->video->getVideoList($page, $state, []);
        }

        $total = $videos->total();

        $module = Module::transform($this->module->id);

        return view($this->view_path . '.index', ['module' => $module, 'base_url' => $this->base_url, 'videos' => $videos, 'state' => $state, 'total' => $total]);
    }

}
