<?php

namespace App\Api\Controllers;

use App\Models\Module;
use App\Models\Order;
use DB;
use Exception;
use Request;

class OrderController extends BaseController
{
    public function transform($order)
    {
        return [
            'id' => $order->id,
            'code' => $order->code,
            'type' => $order->type,
            'pay_type' => $order->pay_type,
            'product_id' => $order->refer->id,
            'product_title' => $order->refer->title,
            'product_unit' => $order->refer->unit,
            'product_image_url' => get_image_url($order->refer->image_url),
            'num' => $order->num,
            'sum' => $order->sum,
            'state' => $order->state,
            'time' => $order->created_at->toDateTimeString(),
        ];
    }

    /**
     * @SWG\Get(
     *   path="/orders/owns",
     *   summary="获取订单列表",
     *   tags={"/orders 订单"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="string"),
     *   @SWG\Parameter(name="state", in="query", required=true, description="订单状态", type="integer"),
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
    public function owns()
    {
        $site_id = Request::get('site_id') ? Request::get('site_id') : 1;
        $state = Request::get('state');
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

        $orders = Order::where('site_id', $site_id)
            ->where('member_id', $member->id)
            ->where('state', $state)
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $orders->transform(function ($order) {
            return $this->transform($order);
        });

        return $this->responseSuccess($orders);
    }

    /**
     * @SWG\Post(
     *   path="/orders/create",
     *   summary="创建订单",
     *   tags={"/orders 订单"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="ID", type="string"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型", type="string"),
     *   @SWG\Parameter(name="num", in="query", required=true, description="数量", type="integer"),
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
        $num = Request::get('num');

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

        //检查数量
        if (!is_numeric($num) || $num <= 0) {
            return $this->responseError('数量非法');
        }

        DB::beginTransaction();
        try {
            //创建订单
            $model->order()->create([
                'site_id' => $model->site_id,
                'title' => $model->title,
                'code' => Order::getCode(),
                'type' => Order::TYPE_BUY,
                'num' => $num,
                'sum' => $model->price * $num,
                'member_id' => $member->id,
                'state' => Order::STATE_UNPAID,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            DB::commit();
        }

        return $this->responseSuccess();
    }

    /**
     * @SWG\Get(
     *   path="/orders/refund",
     *   summary="订单退款详情页",
     *   tags={"/orders 订单"},
     *   @SWG\Parameter(name="code", in="query", required=true, description="订单编号", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function refund()
    {
        $code = Request::get('code');

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        $order = Order::where('code', $code)
            ->where('member_id', $member->id)
            ->first();

        if (!$order) {
            return $this->responseError('此订单ID不存在');
        }

        if ($order->member_id != $member->id) {
            return $this->responseError('当前会员无此订单');
        }

        $site = $order->site;
        $theme = $order->site->mobile_theme->name;

        return view("$theme.orders.refund", compact('site', 'order', 'member'))->__toString();
    }

    /**
     * @SWG\Get(
     *   path="/orders/detail",
     *   summary="获取订单详情页",
     *   tags={"/orders 订单"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="订单ID", type="string"),
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
    public function detail()
    {
        $id = Request::get('id');

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        $order = Order::find($id);
        if (!$order) {
            return $this->responseError('此订单ID不存在');
        }

        if ($order->member_id != $member->id) {
            return $this->responseError('当前会员无此订单');
        }

        $site = $order->site;
        $theme = $order->site->mobile_theme->name;

        return view("$theme.orders.detail", compact('site', 'order', 'member'))->__toString();
    }

    /**
     * @SWG\Get(
     *   path="/orders/info",
     *   summary="获取订单信息",
     *   tags={"/orders 订单"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="订单ID", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function info()
    {
        $id = Request::get('id');

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        $order = Order::where('id', $id)
            ->where('member_id', $member->id)
            ->first();

        if (!$order) {
            return $this->responseError('此订单ID不存在');
        }

        if ($order->member_id != $member->id) {
            return $this->responseError('当前会员无此订单');
        }

        return $this->responseSuccess([
            'id' => $order->id,
            'code' => $order->code,
            'title' => $order->title,
            'pay_type' => $order->pay_type,
            'sum' => $order->sum,
            'state' => $order->state,
            'time' => $order->updated_at->toDateTimeString(),
        ]);
    }

    /**
     * @SWG\Get(
     *   path="/orders/status",
     *   summary="订单状态是否已完成",
     *   tags={"/orders 订单"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="订单ID", type="integer"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function status()
    {
        $id = Request::get('id');

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        $order = Order::find($id);

        if (empty($order)) {
            return $this->responseError('无此订单ID');
        }

        if ($order->state == Order::STATE_COMPLETED) {
            return $this->responseSuccess([
                'id' => $order->id,
                'code' => $order->code,
                'pay_name' => $order->payName(),
                'sum' => $order->sum,
                'state' => $order->state,
                'member_name' => $order->member->name,
                'time' => $order->updated_at->toDateTimeString(),
            ]);
        } else {
            return $this->responseError('未支付');
        }
    }
}