<?php

namespace App\Api\Controllers;

use App\Models\Message;
use Exception;
use Request;

class MessageController extends BaseController
{
    /**
     * @SWG\Get(
     *   path="/messages/owns",
     *   summary="获取我的消息",
     *   tags={"/messages 消息"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="integer"),
     *   @SWG\Parameter(name="last_id", in="query", required=true, description="上次最后一条消息ID", type="integer"),
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
    public function owns()
    {
        $site_id = Request::get('site_id') ? Request::get('site_id') : 1;
        $last_id = Request::get('last_id');

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        $messages = Message::where('site_id', $site_id)
            ->where('member_id', $member->id)
            ->where('state', Message::STATE_NORMAL)
            ->where('id', '>', $last_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $messages->transform(function ($message) {
            return [
                'id' => $message->id,
                'type' => $message->type,
                'title' => $message->title,
                'time' => $message->created_at->toDateTimeString(),
            ];
        });

        return $this->responseSuccess($messages);
    }
}