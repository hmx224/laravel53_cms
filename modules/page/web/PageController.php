<?php

namespace Modules\Page\Web;

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
use Modules\Page\Models\Page;
use Request;
use Response;

/**
 * 页面
 */
class PageController extends BaseController
{
    protected $base_url = '/admin/pages';
    protected $view_path = 'page.views';
    protected $module;

    public function __construct()
    {
        $this->module = Module::where('name', 'Page')->first();
    }

    public function show(Domain $domain, $id)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $page = Page::find($id);
        if (empty($page)) {
            return abort(404);
        }
        $page->incrementClick();

        return view($domain->theme->name . '.page.detail', ['site' => $domain->site, 'page' => $page]);
    }

    public function slug(Domain $domain, $slug)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $page = Page::where('slug', $slug)
            ->first();
        if (empty($page)) {
            return abort(404);
        }
        $page->incrementClick();

        return view($domain->theme->name . '.page.detail', ['site' => $domain->site, 'page' => $page]);
    }

    public function lists(Domain $domain)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $pages = Page::where('state', Page::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->get();

        return view($domain->theme->name . '.page.index', ['site' => $domain->site, 'module' => $this->module, 'pages' => $pages]);
    }

    public function index()
    {
        if (Gate::denies('@page')) {
            return abort(403);
        }

        $module = Module::transform($this->module->id);

        return view($this->view_path . '.index', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function create()
    {
        if (Gate::denies('@page-create')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        return view('admin.contents.create', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function edit($id)
    {
        if (Gate::denies('@page-edit')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        $page = Page::find($id);
        $page->images = null;
        $page->videos = null;
        $page->audios = null;
        $page->tags = $page->tags()->pluck('name')->toArray();

        return view('admin.contents.edit', ['module' => $module, 'content' => $page, 'base_url' => $this->base_url]);
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

        $page = Page::stores($input);

        event(new UserLogEvent(UserLog::ACTION_CREATE . '页面', $page->id, $this->module->model_class));

        \Session::flash('flash_success', '添加成功');
        return redirect($this->base_url);
    }

    public function update($id)
    {
        $input = Request::all();

        $validator = Module::validate($this->module, $input);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $page = Page::updates($id, $input);

        event(new UserLogEvent(UserLog::ACTION_UPDATE . '页面', $page->id, $this->module->model_class));

        \Session::flash('flash_success', '修改成功!');
        return redirect($this->base_url);
    }

    public function comments($refer_id)
    {
        $refer_type = $this->module->model_class;
        return view('admin.comments.list', compact('refer_id', 'refer_type'));
    }

    public function save($id)
    {
        $page = Page::find($id);

        if (empty($page)) {
            return;
        }

        $page->update(Request::all());
    }

    public function sort()
    {
        return Page::sort();
    }

    public function top($id)
    {
        $page = Page::find($id);
        $page->top = !$page->top;
        $page->save();
    }

    public function tag($id)
    {
        $tag = request('tag');
        $page = Page::find($id);
        if ($page->tags()->where('name', $tag)->exists()) {
            $page->tags()->where('name', $tag)->delete();
        } else {
            $page->tags()->create([
                'site_id' => $page->site_id,
                'name' => $tag,
                'sort' => strtotime(Carbon::now()),
            ]);
        }
    }

    public function state()
    {
        $input = request()->all();
        Page::state($input);

        $ids = $input['ids'];
        $stateName = Page::getStateName($input['state']);

        //记录日志
        foreach ($ids as $id) {
            event(new UserLogEvent('变更' . '页面' . UserLog::ACTION_STATE . ':' . $stateName, $id, $this->module->model_class));
        }

        //发布页面
        $site = auth()->user()->site;
        if ($input['state'] == Page::STATE_PUBLISHED) {
            foreach ($ids as $id) {
                $this->dispatch(new PublishPage($site, $this->module, $id));
            }
        }
    }

    public function table()
    {
        return Page::table();
    }

    public function categories()
    {
        return Response::json(Category::tree('', 0, $this->module->id));
    }
}
