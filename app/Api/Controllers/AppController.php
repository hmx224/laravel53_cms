<?php

namespace App\Api\Controllers;

use App\Models\App;
use App\Models\Site;
use Request;


class AppController extends BaseController
{
    /**
     * @SWG\Get(
     *   path="/apps/info",
     *   summary="获取应用程序信息",
     *   tags={"/apps 应用程序"},
     *   @SWG\Parameter(name="app_id", in="query", required=true, description="应用程序ID", type="integer"),
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
    public function info()
    {
        $app_id = Request::get('app_id') ?: Site::ID_DEFAULT;

        $app = App::find($app_id);

        if (!$app) {
            return $this->responseError('此应用程序ID不存在');
        }

        return $this->responseSuccess([
            'id' => $app->id,
            'name' => $app->name,
            'sandbox' => env('APP_DEBUG') ? 1 : 0,
            'android_version' => $app->android_version,
            'android_force' => $app->android_force,
            'android_url' => get_url($app->android_url),
            'ios_version' => $app->ios_version,
            'ios_force' => $app->ios_force,
            'ios_url' => get_url($app->ios_url),
            'logo_url' => get_url($app->logo_url),
        ]);
    }
}