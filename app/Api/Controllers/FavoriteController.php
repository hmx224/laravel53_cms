<?php

namespace App\Api\Controllers;

use App\Models\Favorite;
use App\Models\Module;
use Cache;
use Exception;
use Request;


class FavoriteController extends BaseController
{
    public function transform($favorite)
    {
        $attributes = $favorite->refer->getAttributes();
        $attributes['favorite_id'] = $favorite->id;
        $attributes['images'] = $favorite->refer->images()->transform(function ($item) use ($favorite) {
            return [
                'id' => $item->id,
                'title' => !empty($item->title) ?: $favorite->refer->title,
                'url' => get_image_url($item->url),
                'summary' => $item->summary,
            ];
        });
        foreach ($favorite->refer->getDates() as $date) {
            $attributes[$date] = empty($favorite->refer->$date) ? '' : $favorite->refer->$date->toDateTimeString();
        }
        return $attributes;
    }

    /**
     * @SWG\Get(
     *   path="/favorites",
     *   summary="获取收藏列表",
     *   tags={"/favorites 收藏"},
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

        $favorites = Favorite::where('site_id', $site_id)
            ->where('refer_type', $module->model_class)
            ->where('member_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $favorites = $favorites->filter(function ($favorite) {
            return $favorite->refer()->exists();
        });

        $favorites->transform(function ($favorite) {
            return $this->transform($favorite);
        });

        return $this->responseSuccess(array_values($favorites->toArray()));
    }

    /**
     * @SWG\Post(
     *   path="/favorites/create",
     *   summary="添加收藏",
     *   tags={"/favorites 收藏"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="ID", type="string"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="收藏成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   ),
     *   @SWG\Response(
     *     response="405",
     *     description="收藏数量过多"
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

        //检查收藏记录数是否过多
        $count = Favorite::where('refer_type', $module->model_class)
            ->where('member_id', $member->id)
            ->count();

        if ($count >= 1000) {
            return $this->responseError('收藏数量过多');
        }

        //检查总记录数是否过多
        $count = Favorite::where('refer_type', $module->model_class)
            ->count();

        if ($count >= 1000 * 1000) {
            return $this->responseError('收藏数量过多');
        }

        //判断此收藏是否已存在
        if (!$model->favorites()->where('member_id', $member->id)->count()) {
            //增加收藏记录
            $model->favorites()->create([
                'site_id' => $model->site_id,
                'member_id' => $member->id,
            ]);

            //移除收藏数缓存
            Cache::forget($model->getTable() . "-favorite-$model->id");
        }

        return $this->responseSuccess();
    }

    /**
     * @SWG\Get(
     *   path="/favorites/destroy",
     *   summary="取消收藏",
     *   tags={"/favorites 收藏"},
     *   @SWG\Parameter(name="ids", in="query", required=true, description="ID", type="array", items={"type": "integer"}),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="收藏成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function destroy()
    {
        $ids = explode(',', Request::get('ids'));
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

        $models = call_user_func([$module->model_class, 'findMany'], $ids);

        if (!$models->count()) {
            return $this->responseError('此ID不存在');
        }

        foreach ($models as $model) {
            $favorite = $model->favorites()->where('member_id', $member->id)->first();
            if (!empty($favorite)) {
                $favorite->delete();
            }
        }

        return $this->responseSuccess();
    }

    /**
     * @SWG\Get(
     *   path="/favorites/delete",
     *   summary="删除收藏",
     *   tags={"/favorites 收藏"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="ID", type="integer"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="删除成功"
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

        $favorite = $model->favorites()->where('member_id', $member->id)->first();
        if (!empty($favorite)) {
            $favorite->delete();

            //移除收藏数缓存
            Cache::forget($model->getTable() . "-favorite-$model->id");
        }

        return $this->responseSuccess();
    }

    /**
     * @SWG\Get(
     *   path="/favorites/exist",
     *   summary="是否收藏",
     *   tags={"/favorites 收藏"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="ID", type="string"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="已收藏"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="未收藏"
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

        $favorite = Favorite::where('refer_id', $id)
            ->where('refer_type', $module->model_class)
            ->where('member_id', $member->id)
            ->first();

        if ($favorite) {
            return $this->responseSuccess();
        } else {
            return $this->responseError('未收藏');
        }
    }
}