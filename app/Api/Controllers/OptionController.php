<?php

namespace App\Api\Controllers;

use App\Models\Option;


class OptionController extends BaseController
{
    /**
     * @SWG\Get(
     *   path="/options",
     *   summary="获取所有系统参数",
     *   tags={"/options 系统参数"},
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
    public function lists()
    {
        //获取参数信息
        $options = Option::all();

        return $this->responseSuccess($options);
    }
}