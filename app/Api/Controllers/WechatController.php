<?php

namespace App\Api\Controllers;

use App\Models\Member;
use App\Models\Order;
use Cache;
use Carbon\Carbon;
use EasyWeChat\Foundation\Application;
use Exception;
use Request;

class WechatController extends BaseController
{
    /**
     * @SWG\Get(
     *   path="/wechat/apppay",
     *   summary="APP支付",
     *   tags={"/wechat 微信"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="integer"),
     *   @SWG\Parameter(name="code", in="query", required=true, description="商品编号", type="integer"),
     *   @SWG\Parameter(name="num", in="query", required=true, description="数量", type="integer"),
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
    public function appPay()
    {
        $site_id = Request::get('site_id');
        $code = Request::get('code');
        $num = Request::get('num');

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        //检查商品ID是否存在
        $order = Order::where('code', $code)->where('member_id', $member->id)->first();
        if (empty($order)) {
            return $this->responseError('此订单ID不存在');
        }

        $order->pay_type = Order::PAY_TYPE_WECHAT;
        $order->save();

        //检查数量
        if (!is_numeric($num) || $num <= 0) {
            return $this->responseError('数量非法');
        }

        $options = [
            'app_id' => config('wechat.app_id'),
            'secret' => config('wechat.secret'),
            'token' => config('wechat.token'),
            'aes_key' => config('wechat.aes_key'),
            'payment' => [
                'merchant_id' => config('wechat.payment.merchant_id'),
                'key' => config('wechat.payment.key'),
                'cert_path' => config('wechat.payment.cert_path'),
                'key_path' => config('wechat.payment.key_path'),
                'notify_url' => config('wechat.payment.notify_url'),
            ],
        ];

        $app = new Application($options);
        $order = new \EasyWeChat\Payment\Order([
            'trade_type' => 'APP', // JSAPI，NATIVE，APP...
            'body' => $order->title,
            'detail' => $order->title,
            'out_trade_no' => $order->code,
            'total_fee' => $order->sum * 100,//单位为分，字符串类型
        ]);

        $result = $app->payment->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
            $result['timestamp'] = Carbon::now()->getTimestamp();
            return $this->responseSuccess($app->payment->configForAppPayment($result['prepay_id']));
        }

        if ($result->return_code == 'FAIL') {
            return $this->responseError($result->return_msg, 403);
        }

        return $this->responseError($result->err_code . ' ' . $result->err_code_des, 403);
    }

    /**
     * @SWG\Get(
     *   path="/wechat/scanpay",
     *   summary="扫码支付",
     *   tags={"/wechat 微信"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="integer"),
     *   @SWG\Parameter(name="code", in="query", required=true, description="商品编号", type="integer"),
     *   @SWG\Parameter(name="num", in="query", required=true, description="数量", type="integer"),
     *   @SWG\Parameter(name="sum", in="query", required=true, description="金额", type="integer"),
     *   @SWG\Parameter(name="mobile", in="query", required=true, description="手机号", type="string"),
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
    public function scanPay()
    {
        $site_id = Request::get('site_id');
        $code = Request::get('code');
        $num = Request::get('num');
        $sum = Request::get('sum');
        $mobile = Request::get('mobile');

        $member = Member::where('mobile', $mobile)
            ->first();
        if (empty($member)) {
            return $this->responseError('此会员不存在');
        }

        //检查商品ID是否存在
        $order = Order::where('code', $code)->where('member_id', $member->id)->first();
        if (empty($order)) {
            return $this->responseError('此订单ID不存在');
        }

        //检查数量
        if (!is_numeric($num) || $num <= 0) {
            return $this->responseError('数量非法');
        }

        if (!empty($sum)) {
            if (!is_numeric($sum) || $sum <= 0) {
                return $this->responseError('金额非法');
            }
        }

        //检查数量
        if (!is_numeric($num) || $num <= 0) {
            return $this->responseError('数量非法');
        }

        $order->pay_type = Order::PAY_TYPE_WECHAT;
        $order->save();

        $options = [
            'app_id' => config('wechat.app_id'),
            'secret' => config('wechat.secret'),
            'token' => config('wechat.token'),
            'aes_key' => config('wechat.aes_key'),
            'payment' => [
                'merchant_id' => config('wechat.payment.merchant_id'),
                'key' => config('wechat.payment.key'),
                'cert_path' => config('wechat.payment.cert_path'),
                'key_path' => config('wechat.payment.key_path'),
                'notify_url' => config('wechat.payment.notify_url'),
            ],
        ];

        $app = new Application($options);

        $weChatOrder = new \EasyWeChat\Payment\Order([
            'trade_type' => 'NATIVE', // JSAPI，NATIVE，APP...
            'body' => $order->title,
            'detail' => $order->title,
            'out_trade_no' => $order->code,
            'total_fee' => $order->sum * 100,//TODO * 100,//单位为分，字符串类型
        ]);

        $result = $app->payment->prepare($weChatOrder);

        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
            $result['timestamp'] = Carbon::now()->getTimestamp();
            return $this->responseSuccess([
                'order' => $order,
                'code_url' => $result['code_url'],
            ]);
        }

        if ($result->return_code == 'FAIL') {
            return $this->responseError($result->return_msg, 403);
        }

        return $this->responseError($result->err_code . ' ' . $result->err_code_des, 403);
    }

    /**
     * @SWG\Get(
     *   path="/wechat/refund",
     *   summary="申请退款",
     *   tags={"/wechat 微信"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="integer"),
     *   @SWG\Parameter(name="code", in="query", required=true, description="商品编号", type="integer"),
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
        $site_id = Request::get('site_id');
        $code = Request::get('code');

        //退款编号
        $refundCode = Order::getCode();

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        //检查商品ID是否存在
        $order = Order::where('code', $code)->where('member_id', $member->id)->first();
        if (empty($order)) {
            return $this->responseError('此订单ID不存在');
        }

        //订单超过48小时不能退款
        if ((Carbon::now()->getTimestamp() - $order->created_at->getTimestamp()) > 48 * 3600) {
            return $this->responseError('您的订单付款已超过48小时，无法退款');
        }

        if ($order->state == Order::STATE_ALREADY_REFUND) {
            return $this->responseError('您的订单已经退款');
        }

        $order->state = Order::STATE_WAIT_REFUND;
        $order->refund_code = $refundCode;
        $order->save();

        $options = [
            'app_id' => config('wechat.app_id'),
            'secret' => config('wechat.secret'),
            'token' => config('wechat.token'),
            'aes_key' => config('wechat.aes_key'),
            'payment' => [
                'merchant_id' => config('wechat.payment.merchant_id'),
                'key' => config('wechat.payment.key'),
                'cert_path' => config('wechat.payment.cert_path'),
                'key_path' => config('wechat.payment.key_path'),
                'notify_url' => config('wechat.payment.notify_url'),
            ],
        ];

        $app = new Application($options);

        $result = $app->payment->refundByTransactionId($order->transaction_id, $order->refund_code, $order->sum * 100, $order->sum * 100);

        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
            $result['timestamp'] = Carbon::now()->getTimestamp();

            $order->state = Order::STATE_ALREADY_REFUND;
            $order->save();

            return $this->responseSuccess($order);
        }

        if ($result->return_code == 'FAIL') {
            return $this->responseError($result->return_msg, 403);
        }

        return $this->responseError($result->err_code . ' ' . $result->err_code_des, 403);

    }

    /**
     * 手机异步通知
     */
    public function webNotify()
    {
        \Log::debug('微信支付通知:', Request::all());
        \Log::debug(Request::getContent());

        $options = [
            'app_id' => config('wechat.app_id'),
            'secret' => config('wechat.secret'),
            'token' => config('wechat.token'),
            'aes_key' => config('wechat.aes_key'),
            'payment' => [
                'merchant_id' => config('wechat.payment.merchant_id'),
                'key' => config('wechat.payment.key'),
                'cert_path' => config('wechat.payment.cert_path'),
                'key_path' => config('wechat.payment.key_path'),
            ],
        ];

        $app = new Application($options);

        $response = $app->payment->handleNotify(function ($notify, $successful) {
            $order_code = $notify->out_trade_no;

            $order = Order::where('code', $order_code)
                ->first();
            if (empty($order)) {
                return '此订单ID不存在';
            }

            if ($successful) {
                //检查订单状态是否是未支付
                if ($order->state == Order::STATE_COMPLETED) {
                    return true;
                }

                //订单状态改为已完成
                $order->transaction_id = $notify->transaction_id;
                $order->state = Order::STATE_COMPLETED;
                $order->save();
            }
            \Log::debug('订单完成');
            return true;
        });

        return $response;
    }

    public function signature()
    {
        $url = Request::get('url');

        $app_id = config('wechat.app_id');
        $secret = config('wechat.secret');

        $timestamp = time();
        $nonce_str = md5($timestamp);

        $access_token = Cache::remember('wechat_access_token', 60, function () use ($app_id, $secret) {
            $ret = json_decode(curl_get("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$app_id&secret=$secret"));

            return $ret->access_token;
        });

        $jsapi_ticket = Cache::remember('wechat_jsapi_ticket', 60, function () use ($access_token) {
            $ret = json_decode(curl_get("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi"));

            return $ret->ticket;
        });

        $s = "jsapi_ticket=$jsapi_ticket&noncestr=$nonce_str&timestamp=$timestamp&url=$url";

        $signature = sha1($s);

        return $this->responseSuccess([
            'app_id' => $app_id,
            'timestamp' => $timestamp,
            'nonce_str' => $nonce_str,
            'signature' => $signature,
            'jsapi_ticket' => $jsapi_ticket,
        ]);
    }


}
