<?php

namespace App\Api\Controllers;

use App\Models\Follow;
use App\Models\Module;
use Exception;
use Request;
use Cache;

class FollowController extends BaseController
{
    public function transform($follow)
    {
        $attributes = $follow->refer->getAttributes();
        $attributes['follow_id'] = $follow->id;
        $attributes['images'] = $follow->refer->images()->transform(function ($item) use ($follow) {
            return [
                'id' => $item->id,
                'title' => !empty($item->title) ?: $follow->refer->title,
                'url' => get_image_url($item->url),
                'summary' => $item->summary,
            ];
        });
        foreach ($follow->refer->getDates() as $date) {
            $attributes[$date] = empty($follow->refer->$date) ? '' : $follow->refer->$date->toDateTimeString();
        }
        return $attributes;
    }

    /**
     * @SWG\Get(
     *   path="/follows",
     *   summary="获取我的关注",
     *   tags={"/follows 关注"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="string"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="page_size", in="query", required=true, description="分页大小", type="integer"),
     *   @SWG\Parameter(name="page", in="query", required=true, description="分页序号", type="integer"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
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
        $site_id = Request::get('site_id') ? Request::get('site_id') : 1;
        $type = Request::get('type');
        $page_size = Request::get('page_size');
        $page = Request::get('page');

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

        $follows = Follow::where('site_id', $site_id)
            ->where('refer_type', $module->model_class)
            ->where('member_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $follows = $follows->filter(function ($follow) {
            return $follow->refer()->exists();
        });

        $follows->transform(function ($follow) {
            return $this->transform($follow);
        });

        return $this->responseSuccess(array_values($follows->toArray()));
    }

    /**
     * @SWG\Post(
     *   path="/follows/create",
     *   summary="添加关注",
     *   tags={"/follows 关注"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="ID", type="string"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
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
    public function create()
    {
        $id = Request::get('id');
        $type = Request::get('type');

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

        $count = Follow::where('refer_type', $module->model_class)
            ->where('member_id', $member->id)
            ->count();

        if ($count >= 1000) {
            return $this->responseError('关注数量过多');
        }

        if (!$model->follows()->where('member_id', $member->id)->count()) {
            //增加关注记录
            $model->follows()->create([
                'site_id' => $model->site_id,
                'member_id' => $member->id,
            ]);

            //移除关注数缓存
            Cache::forget($model->getTable() . "-follow-$model->id");
        }

        return $this->responseSuccess();
    }

    /**
     * @SWG\Get(
     *   path="/follows/delete",
     *   summary="取消关注",
     *   tags={"/follows 关注"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="ID", type="integer"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="取消成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function delete()
    {
        $id = Request::get('id');
        $type = Request::get('type');

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

        $follow = $model->follows()->where('member_id', $member->id)->first();
        if (!empty($follow)) {
            $follow->delete();

            //移除收藏数缓存
            Cache::forget($model->getTable() . "-follow-$model->id");
        }

        return $this->responseSuccess();
    }

    /**
     * @SWG\Get(
     *   path="/follows/exist",
     *   summary="是否关注",
     *   tags={"/follows 关注"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="ID", type="string"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="已关注"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="未关注"
     *   )
     * )
     */
    public function exist()
    {
        $id = Request::get('id');
        $type = Request::get('type');

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

        $follow = Follow::where('refer_id', $id)
            ->where('refer_type', $module->model_class)
            ->where('member_id', $member->id)
            ->first();

        if ($follow) {
            return $this->responseSuccess();
        } else {
            return $this->responseError('未关注');
        }
    }
}