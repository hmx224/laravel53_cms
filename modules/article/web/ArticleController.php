<?php

namespace Modules\Article\Web;

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
use Modules\Article\Models\Article;
use Request;
use Response;

/**
 * 文章
 */
class ArticleController extends BaseController
{
    protected $base_url = '/admin/articles';
    protected $view_path = 'article.views';
    protected $module;

    public function __construct()
    {
        $this->module = Module::where('name', 'Article')->first();
    }

    public function show(Domain $domain, $id)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $article = Article::find($id);
        if (empty($article)) {
            return abort(404);
        }
        $article->incrementClick();

        return view($domain->theme->name . '.article.detail', ['site' => $domain->site, 'article' => $article]);
    }

    public function slug(Domain $domain, $slug)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $article = Article::where('slug', $slug)
            ->first();
        if (empty($article)) {
            return abort(404);
        }
        $article->incrementClick();

        return view($domain->theme->name . '.article.detail', ['site' => $domain->site, 'article' => $article]);
    }

    public function lists(Domain $domain)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $articles = Article::where('state', Article::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->get();

        return view($domain->theme->name . '.article.index', ['site' => $domain->site, 'module' => $this->module, 'articles' => $articles]);
    }

    public function index()
    {
        if (Gate::denies('@article')) {
            return abort(403);
        }

        $module = Module::transform($this->module->id);

        return view($this->view_path . '.index', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function create()
    {
        if (Gate::denies('@article-create')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        return view('admin.contents.create', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function edit($id)
    {
        if (Gate::denies('@article-edit')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        $article = Article::find($id);
        $article->images = null;
        $article->videos = null;
        $article->audios = null;
        $article->tags = $article->tags()->pluck('name')->toArray();

        return view('admin.contents.edit', ['module' => $module, 'content' => $article, 'base_url' => $this->base_url, 'back_url' => $this->base_url . '?category_id=' . $article->category_id]);
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

        $article = Article::stores($input);

        event(new UserLogEvent(UserLog::ACTION_CREATE . '文章', $article->id, $this->module->model_class));

        \Session::flash('flash_success', '添加成功');
        return redirect($this->base_url . '?category_id=' . $article->category_id);
    }

    public function update($id)
    {
        $input = Request::all();

        $validator = Module::validate($this->module, $input);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $article = Article::updates($id, $input);

        event(new UserLogEvent(UserLog::ACTION_UPDATE . '文章', $article->id, $this->module->model_class));

        \Session::flash('flash_success', '修改成功!');
        return redirect($this->base_url . '?category_id=' . $article->category_id);
    }

    public function comments($refer_id)
    {
        $refer_type = $this->module->model_class;
        return view('admin.comments.list', compact('refer_id', 'refer_type'));
    }

    public function save($id)
    {
        $article = Article::find($id);

        if (empty($article)) {
            return;
        }

        $article->update(Request::all());
    }

    public function sort()
    {
        return Article::sort();
    }

    public function top($id)
    {
        $article = Article::find($id);
        $article->top = !$article->top;
        $article->save();
    }

    public function tag($id)
    {
        $tag = request('tag');
        $article = Article::find($id);
        if ($article->tags()->where('name', $tag)->exists()) {
            $article->tags()->where('name', $tag)->delete();
        } else {
            $article->tags()->create([
                'site_id' => $article->site_id,
                'name' => $tag,
                'sort' => strtotime(Carbon::now()),
            ]);
        }
    }

    public function state()
    {
        $input = request()->all();
        Article::state($input);

        $ids = $input['ids'];
        $stateName = Article::getStateName($input['state']);

        //记录日志
        foreach ($ids as $id) {
            event(new UserLogEvent('变更' . '文章' . UserLog::ACTION_STATE . ':' . $stateName, $id, $this->module->model_class));
        }

        //发布页面
        $site = auth()->user()->site;
        if ($input['state'] == Article::STATE_PUBLISHED) {
            foreach ($ids as $id) {
                $this->dispatch(new PublishPage($site, $this->module, $id));
            }
        }
    }

    public function table()
    {
        return Article::table();
    }

    public function categories()
    {
        return Response::json(Category::tree('', 0, $this->module->id, false));
    }
}
