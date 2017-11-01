<?php

namespace App\Api\Controllers;

use App\Models\Like;
use App\Models\Module;
use Exception;
use Request;
use Cache;

class LikeController extends BaseController
{
    /**
     * @SWG\Post(
     *   path="/likes/create",
     *   summary="添加点赞数",
     *   tags={"/likes 点赞数"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="ID", type="string"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="flag", in="query", required=true, description="标记(1:点赞,0:取消点赞）", type="integer"),
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
        $flag = Request::get('flag');

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


        $like = $model->likes()->first();

        if (!$like) {
            //增加点赞数
            $model->likes()->create([
                'site_id' => $model->site_id,
                'count' => 1,
            ]);
        }else{
            if ($flag) {
                $like->increment('count');
            } else {
                if ($like->count > 0) {
                    $like->decrement('count');
                }
            }
        }

        //移除点赞数缓存
        Cache::forget($model->getTable() . "-like-$model->id");

        return $this->responseSuccess();
    }
}