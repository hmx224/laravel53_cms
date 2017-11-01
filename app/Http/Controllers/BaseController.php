<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Response;

class BaseController extends Controller
{
    use Helpers;

    protected $code = 200;
    protected $message = 'success';

    public function __construct()
    {
    }

    /**
     * 返回自定义数据
     *
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($data)
    {
        return Response::json($data);
    }

    /**
     * 成功并返回数据
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseSuccess($data = [])
    {
        return $this->response([
            'code' => 200,
            'message' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * 错误并返回错误信息和状态码
     *
     * @param $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseError($message, $code = 404)
    {
        return $this->response([
            'code' => $code,
            'message' => $message,
        ]);
    }

    /**
     * 错误并返回失败信息和状态码
     *
     * @param $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseFail($message, $code = 404)
    {
        return Response::json([
            'code' => $code,
            'message' => $message,
        ], $code);
    }
}