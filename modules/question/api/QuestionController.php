<?php

namespace Modules\Question\Api;

use App\Api\Controllers\BaseController;
use Modules\Question\Models\Question;
use Request;

class QuestionController extends BaseController
{
    public function __construct()
    {
    }

    public function transform($question)
    {
        $attributes = $question->getAttributes();
        $attributes['images'] = $question->images()->transform(function ($item) use ($question) {
            return [
                'id' => $item->id,
                'title' => !empty($item->title) ?: $question->title,
                'url' => get_image_url($item->url),
                'summary' => $item->summary,
            ];
        });
        $attributes['comment_count'] = $question->comment_count;
        $attributes['favorite_count'] = $question->favorite_count;
        $attributes['follow_count'] = $question->follow_count;
        $attributes['like_count'] = $question->like_count;
        $attributes['click_count'] = $question->click_count;
        $attributes['created_at'] = empty($question->created_at) ? '' : $question->created_at->toDateTimeString();
        $attributes['updated_at'] = empty($question->updated_at) ? '' : $question->updated_at->toDateTimeString();
        return $attributes;
    }

    /**
     * @SWG\Get(
     *   path="/questions",
     *   summary="获取问答列表",
     *   tags={"/questions 问答"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="string"),
     *   @SWG\Parameter(name="page_size", in="query", required=true, description="分页大小", type="integer"),
     *   @SWG\Parameter(name="page", in="query", required=true, description="分页序号", type="integer"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function lists()
    {
        $site_id = Request::get('site_id') ? Request::get('site_id') : 1;
        $page_size = Request::get('page_size') ? Request::get('page_size') : 20;
        $page = Request::get('page') ? Request::get('page') : 1;

        $key = "question-list-$site_id-$page_size-$page";

        return cache_remember($key, 1, function () use ($site_id, $page_size, $page) {
            $questions = Question::with('items')
                ->where('site_id', $site_id)
                ->where('state', Question::STATE_PUBLISHED)
                ->orderBy('sort', 'desc')
                ->skip(($page - 1) * $page_size)
                ->limit($page_size)
                ->get();

            $questions->transform(function ($question) {
                return $this->transform($question);
            });

            return $this->responseSuccess($questions);
        });
    }

    /**
     * @SWG\Get(
     *   path="/questions/search",
     *   summary="搜索问答",
     *   tags={"/questions 问答"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="string"),
     *   @SWG\Parameter(name="title", in="query", required=true, description="搜索标题", type="string"),
     *   @SWG\Parameter(name="page_size", in="query", required=true, description="分页大小", type="integer"),
     *   @SWG\Parameter(name="page", in="query", required=true, description="分页序号", type="integer"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function search()
    {
        $site_id = Request::get('site_id') ? Request::get('site_id') : 1;
        $page_size = Request::get('page_size') ? Request::get('page_size') : 20;
        $page = Request::get('page') ? Request::get('page') : 1;
        $title = Request::get('title');

        $questions = Question::with('items')
            ->where('site_id', $site_id)
            ->where('title', 'like', '%' . $title . '%')
            ->where('state', Question::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $questions->transform(function ($question) {
            return $this->transform($question);
        });

        return $this->responseSuccess($questions);
    }

    /**
     * @SWG\Get(
     *   path="/questions/info",
     *   summary="获取问答信息",
     *   tags={"/questions 问答"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="问答ID", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function info()
    {
        $id = Request::get('id');

        Question::click($id);

        $key = "questions-info-$id";

        return cache_remember($key, 1, function () use ($id) {
            $question = Question::find($id);
            if (empty($question)) {
                return $this->responseFail('此ID不存在');
            }

            return $this->responseSuccess($this->transform($question));
        });
    }

    /**
     * @SWG\Get(
     *   path="/questions/detail",
     *   summary="获取问答详情页",
     *   tags={"/questions 问答"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="问答ID", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function detail()
    {
        $id = Request::get('id');

        Question::click($id);

        $key = "questions-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $question = Question::where('id', $id)
                ->where('state', Question::STATE_PUBLISHED)
                ->first();
            $site = $question->site;
            $theme = $question->site->mobile_theme->name;
            $question->content = replace_content_url($question->content);
            return view("$theme.question.detail", compact('site', 'question'))->__toString();
        });
    }

    /**
     * @SWG\Get(
     *   path="/questions/share",
     *   summary="获取问答分享页",
     *   tags={"/questions 问答"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="问答ID", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function share()
    {
        $id = Request::get('id');

        Question::click($id);

        $key = "questions-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $question = Question::where('id', $id)
                ->where('state', Question::STATE_PUBLISHED)
                ->first();
            $site = $question->site;
            $theme = $question->site->mobile_theme->name;
            $question->content = replace_content_url($question->content);
            $share = 1;
            return view("$theme.question.detail", compact('site', 'question', 'share'))->__toString();
        });
    }
}