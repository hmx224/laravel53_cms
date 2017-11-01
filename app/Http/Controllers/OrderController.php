<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Gate;
use Request;
use Response;

class OrderController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        if (Gate::denies('@order')) {
            $this->middleware('deny403');
        }

        return view('admin.orders.index');
    }

    public function charge()
    {
        if (Gate::denies('@order-charge')) {
            \Session::flash('flash_warning', '无此访问权限');
            return redirect()->back();
        }
        return view('orders.charge');
    }

    public function show($id)
    {
        $order = Order::findOrFail($id);
        return view('mobile.orders.detail', compact('order'));
    }

    public function table()
    {
        $filters = Request::all();

        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $orders = Order::owns()
            ->filter($filters)
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $total = Order::owns()
            ->filter($filters)
            ->count();

        $orders->transform(function ($order) {
            return [
                'id' => $order->id,
                'refer_id' => $order->refer_id,
                'code' => $order->code,
                'name' => !empty($order->title) ? $order->title : $order->refer->title,
                'num' => $order->num,
                'fee' => $order->sum,
                'type' => $order->type,
                'type_name' => $order->typeName(),
                'pay_type' => $order->pay_type,
                'pay_name' => $order->payName(),
                'memo' => $order->memo,
                'member_id' => $order->member_id,
                'nick_name' => empty($order->member) ? '' : $order->member->nick_name,
                'mobile' => empty($order->member) ? '' : $order->member->mobile,
                'state' => $order->state,
                'state_name' => $order->stateName(),
                'user_name' =>  empty($order->user) ? '' : $order->user->name,
                'created_at' => $order->created_at->toDateTimeString(),
                'updated_at' => $order->updated_at->toDateTimeString(),
            ];
        });

        $ds = new \stdClass();
        $ds->total = $total;
        $ds->rows = $orders;

        return Response::json($ds);
    }
}
