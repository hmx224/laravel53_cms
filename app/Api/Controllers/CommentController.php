<?php

namespace App\Api\Controllers;

use App\Models\Comment;
use App\Models\Item;
use App\Models\Module;
use App\Models\Option;
use Cache;
use Exception;
use Request;

class CommentController extends BaseController
{
    public function transform($comment)
    {
        return [
            'id' => $comment->id,
            'children' => $comment->children()->where('state', Comment::STATE_PASSED)->orderBy('id', 'desc')->get()->transform(function ($child) {
                return [
                    'id' => $child->id,
                    'content' => $child->content,
                    'likes' => $child->likes,
                    'member_id' => $child->member->id,
                    'member_name' => $child->member->name,
                    'member_type' => $child->member->type,
                    'nick_name' => $child->member->nick_name,
                    'avatar_url' => get_image_url($child->member->avatar_url),
                    'time' => $child->created_at->toDateTimeString(),
                ];
            }),
            'images' => $comment->images()->transform(function ($file) use ($comment) {
                return [
                    'id' => $file->id,
                    'refer_id' => $file->id,
                    'title' => !empty($file->title) ?: $comment->title,
                    'url' => get_image_url($file->url),
                    'summary' => $file->summary,
                ];
            }),
            'content' => $comment->content,
            'likes' => $comment->likes,
            'member_name' => $comment->member->name,
            'nick_name' => $comment->member->nick_name,
            'avatar_url' => get_image_url($comment->member->avatar_url),
            'time' => $comment->created_at->toDateTimeString(),
        ];
    }

    /**
     * @SWG\Get(
     *   path="/comments",
     *   summary="获取评论列表",
     *   tags={"/comments 评论"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="ID", type="string"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="page_size", in="query", required=true, description="分页大小", type="integer"),
     *   @SWG\Parameter(name="page", in="query", required=true, description="分页序号", type="integer"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function lists()
    {
        $id = Request::get('id');
        $type = Request::get('type');
        $page_size = Request::get('page_size') ? Request::get('page_size') : 20;
        $page = Request::get('page') ? Request::get('page') : 1;

        $module = Module::findByName($type);
        if (!$module) {
            return $this->responseError('此类型不存在');
        }

        $model = call_user_func([$module->model_class, 'find'], $id);

        if (empty($model)) {
            return $this->responseError('此ID不存在');
        }

        $total = $model->comments()
            ->where('state', Comment::STATE_PASSED)
            ->count();

        $comments = $model->comments()
            ->where('state', Comment::STATE_PASSED)
            ->orderBy('id', 'desc')
            ->forPage($page, $page_size)
            ->get();

        $comments->transform(function ($comment) {
            return $this->transform($comment);
        });

        return $this->response([
            'status_code' => 200,
            'message' => 'success',
            'total' => $total,
            'data' => $comments
        ]);
    }

    /**
     * @SWG\Post(
     *   path="/comments/create",
     *   summary="发表评论",
     *   tags={"/comments 评论"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="ID", type="string"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="content", in="query", required=true, description="内容", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="评论成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function create()
    {
        $id = Request::get('id');
        $type = Request::get('type');
        $content = Request::get('content');

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        $module = Module::findByName($type);
        if (!$module) {
            return $this->responseError('此类型不存在');
        }

        $model = call_user_func([$module->model_class, 'find'], $id);

        if (empty($model)) {
            return $this->responseError('此ID不存在');
        }

        //是否免审核
        $option = Option::getValue(Option::COMMENT_REQUIRE_PASS);

        //增加评论记录
        $model->comments()->create([
            'site_id' => $model->site_id,
            'content' => $content,
            'ip' => get_client_ip(),
            'member_id' => $member->id,
            'state' => $option ? Comment::STATE_NORMAL : Comment::STATE_PASSED
        ]);

        //移除评论数缓存
        Cache::forget($model->getTable() . "-comment-$model->id");

        return $this->responseSuccess();
    }
}