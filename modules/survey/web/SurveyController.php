<?php

namespace Modules\Survey\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\SurveyRequest;
use App\Models\Item;
use App\Models\Module;
use Auth;
use Carbon\Carbon;
use Gate;
use Modules\Survey\Models\Subject;
use Modules\Survey\Models\Survey;

class SurveyController extends BaseController
{
    protected $base_url = '/admin/surveys';
    protected $view_path = 'survey.views';
    protected $module;

    public function __construct()
    {
        $this->module = Module::where('name', 'Survey')->first();
    }

    public function index()
    {
        $this->middleware('auth');
        if (Gate::denies('@survey')) {
            $this->middleware('deny403');
        }
        $module = Module::transform($this->module->id);

        return view($this->view_path . '.index', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function destroy($id)
    {
        if (Gate::denies('@survey-delete')) {
            \Session::flash('flash_warning', '无此操作权限');
            return;
        }

        $vote = Survey::find($id);
        $vote->state = Survey::STATE_DELETED;
        $vote->save();
        \Session::flash('flash_success', '删除成功');
    }

    public function create()
    {
        if (Gate::denies('@survey-create')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);
        return view($this->view_path . '.create', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function store(SurveyRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['state'] = Survey::STATE_NORMAL;
        $data['site_id'] = Auth::user()->site_id;
        $survey = Survey::create($data);

        $subject = $data['item_subject']; //子题目

        //判断有无item_subject,存入题目信息
        if (array_key_exists('item_subject', $data)) {
            foreach ($subject as $key => $item_subject) {
                //存在题目存选项
                if ($item_subject == '') {
                    continue;
                }
                $data1 = [
                    'type' => Item::TYPE_IMAGE,
                    'title' => $item_subject,
                    'summary' => $data['summary_subject'][$key],
                    'url' => $data['item_url_subject'][$key],
                    'sort' => $key
                ];
                $subject = $survey->subjects()->create($data1);

                //存入子选项信息
                if (array_key_exists('item_title' . ($key + 1), $data)) {
                    foreach ($data['item_title' . ($key + 1)] as $k => $item_title) {
                        $data2 = [
                            'type' => Item::TYPE_IMAGE,
                            'title' => $item_title,
                            'summary' => $data['summary' . ($key + 1)][$k],
                            'url' => $data['item_url' . ($key + 1)][$k],
                            'sort' => $k
                        ];
                        $subject->items()->create($data2);
                    }
                }
            }
        }

        return redirect($this->base_url)->with('flash_success', '新增成功！');
    }

    public function show($id)
    {
        $survey = Survey::with('subjects')->find($id);

        $site = $survey->site;

        $theme = $site->mobile_theme->name;

        return view("$theme.surveys.share", compact('survey', 'site'));

    }

    public function edit($id)
    {
        if (Gate::denies('@survey-edit')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }
        // 一个问卷对应多个题目,一个题目对应多个选项
        $survey = Survey::with('subjects')->find($id);

        $module = Module::transform($this->module->id);

        return view($this->view_path . '.edit', ['module' => $module, 'survey' => $survey]);

    }

    public function update(SurveyRequest $request, $id)
    {
        $survey = Survey::with('subjects')->find($id);

        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['state'] = Survey::STATE_NORMAL;
        $data['site_id'] = Auth::user()->site_id;

        if ($data['link_type'] == Survey::LINK_TYPE_NONE) {
            $data['link'] = '';
        }
        $survey->update($data);

        //删除选项
        foreach ($survey->subjects as $k => $item) {
            $item->items()->whereNotIn('id', $data['item_id' . ($k + 1)])->delete();
        }

        $subject = $data['item_subject'];

        //题目 更新
        if (array_key_exists('item_subject', $data)) {
            foreach ($subject as $key => $item_subject) {
                if ($item_subject == '') {
                    continue;
                }
                $data_subject = [
                    'type' => Item::TYPE_IMAGE,
                    'title' => $item_subject,
                    'summary' => $data['summary_subject'][$key],
                    'url' => $data['item_url_subject'][$key],
                    'sort' => $key
                ];

                if (empty($data['item_id_subject'][$key])) {
                    $title = $survey->subjects()->create($data_subject);
                    //存储题目的子选项
                    if (array_key_exists('item_title' . ($key + 1), $data)) {
                        foreach ($data['item_title' . ($key + 1)] as $k => $item) {
                            if ($item == '') {
                                continue;
                            }
                            $title->items()->create([
                                'type' => Item::TYPE_IMAGE,
                                'title' => $item,
                                'summary' => $data['summary' . ($key + 1)][$k],
                                'url' => $data['item_url' . ($key + 1)][$k],
                                'sort' => $key
                            ]);
                        }
                    }

                } else {
                    $subject = $survey->subjects()->find($data['item_id_subject'][$key]);
                    $subject->update($data_subject);
                    //存储题目的子选项
                    if (array_key_exists('item_title' . ($key + 1), $data)) {
                        foreach ($data['item_title' . ($key + 1)] as $k => $item) {
                            if ($item == '') {
                                continue;
                            }
                            $data_item = [
                                'type' => Item::TYPE_IMAGE,
                                'title' => $item,
                                'summary' => $data['summary' . ($key + 1)][$k],
                                'url' => $data['item_url' . ($key + 1)][$k],
                                'sort' => $key
                            ];
                            if (empty($data['item_id' . ($key + 1)][$k])) {
                                $subject->items()->create($data_item);
                            } else {
                                $item2 = Subject::with('items')->find($data['item_id' . ($key + 1)][$k]);
                                $item2->update($data_item);
                            }
                        }
                    }
                }
            }
        }
        return redirect($this->base_url)->with('flash_success', '编辑成功！');
    }

    public function table()
    {
        return Survey::table();
    }

    public function state()
    {
        Survey::state(request()->all());
    }

    public function sort()
    {
        return Survey::sort();
    }

    public function statistic($id)
    {
        $survey = Survey::find($id);
        return view('admin.surveys.show', compact('survey'));
    }

    public function top($id)
    {
        $survey = Survey::find($id);
        $survey->top = !$survey->top;
        $survey->save();
    }

    public function tag($id)
    {
        $tag = request('tag');
        $survey = Survey::find($id);
        if ($survey->tags()->where('name', $tag)->exists()) {
            $survey->tags()->where('name', $tag)->delete();
        } else {
            $survey->tags()->create([
                'site_id' => $survey->site_id,
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

}
