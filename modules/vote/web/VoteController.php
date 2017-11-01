<?php

namespace Modules\Vote\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\VoteRequest;
use App\Models\Item;
use App\Models\Module;
use Modules\Vote\Models\Vote;
use Auth;
use Carbon\Carbon;
use Gate;

class VoteController extends BaseController
{
    protected $base_url = '/admin/votes';
    protected $view_path = 'vote.views';
    protected $module;

    public function __construct()
    {
        $this->module = Module::where('name', 'Vote')->first();
    }

    public function index()
    {
        if (Gate::denies('@vote')) {
            $this->middleware('deny403');
        }

        $module = Module::transform($this->module->id);
        return view($this->view_path . '.index', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function create()
    {
        if (Gate::denies('@vote-create')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);
        return view($this->view_path . '.create', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function store(VoteRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['state'] = Vote::STATE_NORMAL;
        $data['site_id'] = Auth::user()->site_id;
        $vote = Vote::create($data);

        foreach ($data['item_title'] as $k => $item_title) {
            $vote->items()->create([
                'type' => Item::TYPE_IMAGE,
                'title' => $item_title,
                'url' => $data['item_url'][$k],
                'summary' => $data['summary'][$k],
                'sort' => $k,
            ]);
        }

        return redirect($this->base_url)->with('flash_success', '新增成功！');
    }

    public function show($id)
    {
        $vote = Vote::find($id);
        $site = $vote->site;
        $theme = $site->mobile_theme->name;

        return view("$theme.votes.share", compact('vote', 'site'));
    }

    public function edit($id)
    {
        if (Gate::denies('@vote-edit')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $vote = Vote::with('items')->find($id);

        $module = Module::transform($this->module->id);
        return view($this->view_path . '.edit', ['module' => $module, 'vote' => $vote, 'base_url' => $this->base_url]);
    }

    public function update(VoteRequest $request, $id)
    {
        $vote = Vote::with('items')->find($id);
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['state'] = Vote::STATE_NORMAL;
        $data['site_id'] = Auth::user()->site_id;

        if ($data['link_type'] == Vote::LINK_TYPE_NONE) {
            $data['link'] = '';
        }

        $vote->update($data);

        $vote->items()->whereNotIn('id', $data['item_id'])->delete();

        foreach ($data['item_title'] as $k => $item_title) {
            if (!empty(trim($item_title)) || !empty(trim($data['item_url'][$k]))) {
                if (empty($data['item_id'][$k])) {
                    $vote->items()->create([
                        'type' => Item::TYPE_IMAGE,
                        'title' => $item_title,
                        'url' => $data['item_url'][$k],
                        'summary' => $data['summary'][$k],
                        'sort' => $k,
                    ]);
                } else {
                    $item = $vote->items()->where('id', $data['item_id'][$k])->first();
                    $item->update([
                        'type' => Item::TYPE_IMAGE,
                        'title' => $item_title,
                        'url' => $data['item_url'][$k],
                        'summary' => $data['summary'][$k],
                        'sort' => $k,
                    ]);
                }
            }
        }

        return redirect($this->base_url)->with('flash_success', '编辑成功！');
    }

    public function table()
    {
        return Vote::table();
    }

    public function sort()
    {
        return Vote::sort();
    }

    public function top($id)
    {
        $vote = Vote::find($id);
        $vote->top = !$vote->top;
        $vote->save();
    }

    public function tag($id)
    {
        $tag = request('tag');
        $vote = Vote::find($id);
        if ($vote->tags()->where('name', $tag)->exists()) {
            $vote->tags()->where('name', $tag)->delete();
        } else {
            $vote->tags()->create([
                'site_id' => $vote->site_id,
                'name' => $tag,
                'sort' => strtotime(Carbon::now()),
            ]);
        }
    }

    public function comments($refer_id)
    {
        $refer_type = $this->module->model_class;
        return view('admin.comments.list', compact('refer_id', 'refer_type'));
    }

    public function state()
    {
        Vote::state(request()->all());
    }
}
