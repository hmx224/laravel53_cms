<?php

namespace App\Api\Controllers;

use App\Models\Member;
use App\Models\Order;
use Carbon\Carbon;
use Exception;
use Omnipay\Omnipay;
use Request;

class AlipayController extends BaseController
{
    /**
     * @SWG\Get(
     *   path="/alipay/apppay",
     *   summary="APP支付",
     *   tags={"/alipay 支付宝"},
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
     *     description="没有找到"
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

        $order->pay_type = Order::PAY_TYPE_ALIPAY;
        $order->save();

        //检查数量
        if (!is_numeric($num) || $num <= 0) {
            return $this->responseError('数量非法');
        }

        //发送到支付宝网关
        $gateway = Omnipay::create('Alipay_LegacyApp');
        $gateway->setPartner(config('alipay.partner_id'));
        $gateway->setSellerId(config('alipay.partner_id'));
        $gateway->setPrivateKey(config('alipay.private_key'));
        $gateway->setAlipayPublicKey(config('alipay.alipay_public_key'));
        $gateway->setReturnUrl(config('alipay.return_url'));
        $gateway->setNotifyUrl(config('alipay.notify_url'));

        $request = $gateway->purchase([
            'out_trade_no' => $order->code,
            'subject' => $order->title,
            'total_fee' => $order->sum
        ]);

        $response = $request->send();

        return $this->responseSuccess($response->getOrderString());
    }

    /**
     * @SWG\Get(
     *   path="/alipay/wappay",
     *   summary="手机网站支付",
     *   tags={"/alipay 支付宝"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="integer"),
     *   @SWG\Parameter(name="code", in="query", required=true, description="商品编号", type="integer"),
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
    public function wapPay()
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

        $order->pay_type = Order::PAY_TYPE_ALIPAY;
        $order->save();

        //检查数量
        if (!is_numeric($num) || $num <= 0) {
            return $this->responseError('数量非法');
        }

        //发送到支付宝网关
        $gateway = Omnipay::create('Alipay_LegacyWap');
        $gateway->setPartner(config('alipay.partner_id'));
        $gateway->setSellerId(config('alipay.partner_id'));
        $gateway->setPrivateKey(config('alipay.private_key'));
        $gateway->setAlipayPublicKey(config('alipay.alipay_public_key'));
        $gateway->setReturnUrl(config('alipay.return_url'));
        $gateway->setNotifyUrl(config('alipay.notify_url'));

        $request = $gateway->purchase([
            'out_trade_no' => $order->code,
            'subject' => $order->title,
            'total_fee' => $order->sum,
            'show_url' => '',
        ]);

        $request->setSignType('RSA');
        $response = $request->send();

        $response->redirect();
    }

    /**
     * @SWG\Get(
     *   path="/alipay/scanpay",
     *   summary="扫码支付",
     *   tags={"/alipay 支付宝"},
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
     *     description="没有找到"
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

        $order->pay_type = Order::PAY_TYPE_ALIPAY;
        $order->save();

        //检查数量
        if (!is_numeric($num) || $num <= 0) {
            return $this->responseError('数量非法');
        }

        if (!empty($sum)) {
            if (!is_numeric($sum) || $sum <= 0) {
                return $this->responseError('金额非法');
            }
        }

        //发送到支付宝网关
        $gateway = Omnipay::create('Alipay_LegacyExpress');
        $gateway->setSellerEmail(config('alipay.seller_email'));
        $gateway->setPartner(config('alipay.partner_id'));
        $gateway->setPrivateKey(config('alipay.private_key'));
        $gateway->setAlipayPublicKey(config('alipay.alipay_public_key'));
        $gateway->setReturnUrl(config('alipay.return_url'));
        $gateway->setNotifyUrl(config('alipay.notify_url'));

        $request = $gateway->purchase([
            'out_trade_no' => $order->code,
            'subject' => $order->title,
            'total_fee' => $order->sum,
            'show_url' => '',
        ]);

        $request->setSignType('RSA');
        $response = $request->send();

        $response->redirect();
    }


    public function refunds()
    {
        $gateway = Omnipay::gateway('unionpay');
        $response = $gateway->refund(['orderId' => '20150815121214', 'txnTime' => '20150815121214', 'txnAmt' => '200',])->send();
        var_dump($response->isSuccessful());
        var_dump($response->getData());
    }

    /**
     * @SWG\Get(
     *   path="/alipay/refund",
     *   summary="申请退款",
     *   tags={"/alipay 支付宝"},
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

        //发送到支付宝网关，由于Alipay_LegacyApp是即时到帐退款，所以用Alipay_AopApp
        $gateway = Omnipay::create('Alipay_AopApp');
        $gateway->setAppId(config('alipay.app_id'));
        $gateway->setSignType('RSA');
        $gateway->setPrivateKey(config('alipay.private_key'));

        $request = $gateway->refund(
            [
                'biz_content' => [
                    'refund_amount' => $order->sum,
                    'out_trade_no' => $order->code
                ]
            ]
        );

        try {
            $response = $request->send();
            $responseData = $response->getData();
            $refundData = $responseData['alipay_trade_refund_response'];

            \Log::debug('退款通知:', $responseData);

            if ($response->isSuccessful()) {
                $order->state = Order::STATE_ALREADY_REFUND;
                $order->save();

                return $this->responseSuccess($order);
            } else {
                return $this->responseError($refundData['sub_code'] . ' ' . $refundData['sub_msg'], 403);
            }
        } catch (Exception $e) {
            die('fail');
        }
    }

    public function webReturn()
    {
        //发送到支付宝网关
        $gateway = Omnipay::create('Alipay_LegacyWap');
        $gateway->setPartner(config('alipay.partner_id'));
        $gateway->setSellerId(config('alipay.partner_id'));
        $gateway->setPrivateKey(config('alipay.private_key'));
        $gateway->setAlipayPublicKey(config('alipay.alipay_public_key'));
        $gateway->setReturnUrl(config('alipay.return_url'));
        $gateway->setNotifyUrl(config('alipay.notify_url'));

        $request = $gateway->completePurchase();
        $request->setParams(array_merge($_POST, $_GET)); //Don't use $_REQUEST for may contain $_COOKIE

        try {
            $response = $request->send();

            if ($response->isPaid()) {
                $order_code = $response->data('out_trade_no');

                $order = Order::where('code', $order_code)
                    ->first();
                //检查订单状态是否是未支付
                if ($order->state == Order::STATE_COMPLETED) {
                    return true;
                }

                $order->state = Order::STATE_COMPLETED;
                $order->save();

                \Log::debug('订单完成');

                die('success');
            } else {
                die('fail');
            }
        } catch (Exception $e) {
            die('fail');
        }
    }

    public function webNotify()
    {
        //发送到支付宝网关
        $gateway = Omnipay::create('Alipay_LegacyApp');
        $gateway->setPartner(config('alipay.partner_id'));
        $gateway->setSellerId(config('alipay.partner_id'));
        $gateway->setPrivateKey(config('alipay.private_key'));
        $gateway->setAlipayPublicKey(config('alipay.alipay_public_key'));
        $gateway->setReturnUrl(config('alipay.return_url'));
        $gateway->setNotifyUrl(config('alipay.notify_url'));

        $request = $gateway->completePurchase();
        $request->setParams($_POST);

        try {
            $response = $request->send();
            if ($response->isPaid()) {
                $order_code = $response->data('out_trade_no');
                \Log::debug($order_code);

                $order = Order::where('code', $order_code)
                    ->first();
                //检查订单状态是否是未支付
                if ($order->state == Order::STATE_COMPLETED) {
                    return true;
                }

                \Log::debug('支付宝通知:', Request::all());
                \Log::debug(Request::getContent());

                //订单状态改为已完成
                $order->transaction_id = $response->data('trade_no');
                $order->state = Order::STATE_COMPLETED;
                $order->save();

                \Log::debug('订单完成');

                die('success');
            } else {
                die('fail');
            }
        } catch (Exception $e) {
            die('fail');
        }
    }
}
