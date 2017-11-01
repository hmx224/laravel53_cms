<?php

namespace App\Api\Controllers;


use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Response;
use Request;

/**
 * @SWG\Swagger(
 *     schemes={"http","https"},
 *     basePath="/api",
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="CMS API v1",
 *         termsOfService="",
 *     ),
 * )
 */
class BaseController extends Controller
{
    use Helpers;

    protected $status_code = 200;
    protected $message = 'success';

    public function __construct()
    {
        header("Cache-Control:no-cache");
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
            'status_code' => 200,
            'message' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * 错误并返回错误信息和状态码
     *
     * @param $message
     * @param int $status_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseError($message, $status_code = 404)
    {
        \Log::debug('Error IP: ' . get_client_ip() . ', '. $message);
        return $this->response([
            'status_code' => $status_code,
            'message' => $message,
        ]);
    }

    /**
     * 错误并返回失败信息和状态码
     *
     * @param $message
     * @param int $status_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseFail($message, $status_code = 404)
    {
        return Response::json([
            'status_code' => $status_code,
            'message' => $message,
        ], $status_code);
    }
}