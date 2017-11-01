<?php

namespace App\Api\Controllers;

use App\Libraries\Ip\Ip;
use App\Models\IpLog;
use App\Models\PvLog;
use App\Models\Site;
use App\Models\UvLog;
use Carbon\Carbon;
use Request;

class AccessController extends BaseController
{
    /**
     * @SWG\Get(
     *   path="/access/log",
     *   summary="记录页面访问日志",
     *   tags={"/access 访问"},
     *   @SWG\Parameter(name="app_key", in="query", required=false, description="App Key", type="string"),
     *   @SWG\Parameter(name="url", in="query", required=true, description="URL", type="string"),
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
    public function log()
    {
        $app_key = Request::get('app_key');
        $url = Request::get('url');
        $title = Request::get('title');
        $ip = get_client_ip();
        $browser = get_ua_browser();
        $os = get_ua_os();

        //TODO 验证app_key

        //获取站点ID
        $sites = cache_remember('site-all', 1, function () {
            return Site::all();
        });

        $site_id = 0;
        foreach ($sites as $site) {
            if (str_contains(strtolower($url), strtolower($site->domain))) {
                $site_id = $site->id;
                break;
            }
        }

        if ($site_id == 0) {
            return $this->responseError('此域名未注册');
        }

        //记录PV日志
        PvLog::create([
            'site_id' => $site_id,
            'title' => $title,
            'url' => $url,
            'ip' => $ip
        ]);

        //记录IP日志
        $area = Ip::find($ip);
        $log = IpLog::where('ip', $ip)
            ->where('created_at', '>=', Carbon::now()->toDateString())
            ->where('created_at', '<', Carbon::now()->addDay()->toDateString())
            ->first();
        if (empty($log)) {
            IpLog::create([
                'site_id' => $site_id,
                'ip' => $ip,
                'country' => $area[0],
                'province' => $area[1],
                'city' => $area[2],
                'count' => 1,
            ]);
        } else {
            $log->increment('count');
        }

        //记录UV日志
        if (isset($_COOKIE['uvid'])) {
            $uvid = $_COOKIE['uvid'];
            $log = UvLog::where('uvid', $uvid)
                ->where('created_at', '>=', Carbon::now()->toDateString())
                ->where('created_at', '<', Carbon::now()->addDay()->toDateString())
                ->first();
            if (empty($log)) {
                UvLog::create([
                    'site_id' => $site_id,
                    'uvid' => $uvid,
                    'browser' => $browser,
                    'os' => $os,
                ]);
            }
        }

        return $this->responseSuccess();
    }
}