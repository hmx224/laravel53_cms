<?php

namespace App\Http\Controllers;

use App\Models\IpLog;
use App\Models\Member;
use App\Models\PvLog;
use App\Models\UvLog;
use Carbon\Carbon;
use DB;

class AdminController extends BaseController
{
    public function __construct()
    {
    }

    public function login()
    {
        return view('admin.login');
    }

    public function dashboard()
    {
        //最近注册会员
        $members = Member::take(8)
            ->orderBy('created_at', 'desc')
            ->get();

        //当天页面排行
        $pages = PvLog::selectRaw('title,url,count(*) as clicks')
            ->take(8)
            ->where('created_at', '>=', Carbon::now()->toDateString())
            ->where('created_at', '<', Carbon::now()->addDay()->toDateString())
            ->groupBy('url')
            ->get();
        $pages = $pages->sortByDesc('clicks');
        $sum = $pages->sum('clicks');

        //计算百分比
        $badges = ['bg-red', 'bg-yellow', 'bg-light-blue', 'bg-default', 'bg-default', 'bg-default'];
        $i = 0;
        foreach ($pages as $page) {
            $page->percent = round($page->clicks / max(1, $sum) * 100, 2) . '%';
            $page->badge = $badges[min($i++, count($badges) - 1)];
        };

        //统计
        $pv = PvLog::count();
        $uv = UvLog::count();
        $ip = IpLog::count();
        $rm = Member::count();

        return view('admin.dashboard', compact('pv', 'uv', 'ip', 'rm', 'members', 'pages'));
    }

    /**
     * 获取当天浏览器访问(UV)统计数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function browsers()
    {
        $logs = UvLog::selectRaw('browser as name, count(*) as value')
            ->where('created_at', '>=', Carbon::now()->toDateString())
            ->where('created_at', '<', Carbon::now()->addDay()->toDateString())
            ->groupBy('browser')
            ->get();

        $logs = $logs->sortByDesc('value');

        return $this->response([
            'browsers' => $logs->pluck('name')->toArray(),
            'data' => array_values($logs->toArray()),
        ]);
    }

    /**
     * 获取最近7天地区访问(IP)统计数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function areas()
    {
        $dates = [];
        $data = [];
        $max = 0;
        $areas = ['北京', '天津', '河北', '山西', '内蒙古', '辽宁', '吉林', '黑龙江', '上海', '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南', '广东', '广西', '海南', '重庆', '四川', '贵州', '云南', '西藏', '陕西', '甘肃', '青海', '宁夏', '新疆', '台湾'];

        //数组初始赋值
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->addDays($i * (-1))->toDateString();;
            $dates[] = $date;
            foreach ($areas as $area) {
                $data[$date][] = ['name' => $area, 'value' => 0];
            }
        }

        $logs = DB::select('select date_format(created_at, \'%Y-%m-%d\') as date, province as name, sum(count) as value from cms_ip_logs where date_sub(curdate(), interval 7 day) <= date(`created_at`) group by province,date_format(created_at, \'%Y-%m-%d\')');

        foreach ($logs as $log) {
            for ($i = 0; $i < count($areas); $i++) {
                if ($areas[$i] == $log->name) {
                    $data[$log->date][$i] = ['name' => $log->name, 'value' => $log->value];
                    break;
                }
            }
            $max = max($log->value, $max);
        }

        return $this->response([
            'date' => $dates,
            'max' => $max,
            'data' => $data,
        ]);
    }

    /**
     * 获取最近7天小时访问统计数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function hours()
    {
        $dates = [];
        $pvs = [];
        $uvs = [];
        $ips = [];
        $rms = [];
        $max = 0;

        //数组初始赋值
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->addDays($i * (-1))->toDateString();;
            $dates[] = $date;
            for ($j = 0; $j < 24; $j++) {
                $pvs[$date][$j] = ['value' => 0];
                $uvs[$date][$j] = ['value' => 0];
                $ips[$date][$j] = ['value' => 0];
                $rms[$date][$j] = ['value' => 0];
            }
        }

        //PV
        $logs = DB::select('select date_format(created_at, \'%Y-%m-%d\') as date, date_format(created_at, \'%H\') as name, count(*) as value from cms_pv_logs where date_sub(curdate(), INTERVAL 7 DAY) <= date(`created_at`) group by date_format(created_at, \'%Y-%m-%d\'), date_format(created_at, \'%H\')');
        foreach ($logs as $log) {
            $pvs[$log->date][(int)$log->name] = ['value' => $log->value];
            $max = max($log->value, $max);
        }

        //UV
        $logs = DB::select('select date_format(created_at, \'%Y-%m-%d\') as date, date_format(created_at, \'%H\') as name, count(*) as value from cms_uv_logs where date_sub(curdate(), INTERVAL 7 DAY) <= date(`created_at`) group by date_format(created_at, \'%Y-%m-%d\'), date_format(created_at, \'%H\')');
        foreach ($logs as $log) {
            $uvs[$log->date][(int)$log->name] = ['value' => $log->value];
        }

        //IP
        $logs = DB::select('select date_format(created_at, \'%Y-%m-%d\') as date, date_format(created_at, \'%H\') as name, count(*) as value from cms_ip_logs where date_sub(curdate(), INTERVAL 7 DAY) <= date(`created_at`) group by date_format(created_at, \'%Y-%m-%d\'), date_format(created_at, \'%H\')');
        foreach ($logs as $log) {
            $ips[$log->date][(int)$log->name] = ['value' => $log->value];
        }

        //RM
        $logs = DB::select('select date_format(created_at, \'%Y-%m-%d\') as date, date_format(created_at, \'%H\') as name, count(*) as value from cms_members where date_sub(curdate(), INTERVAL 7 DAY) <= date(`created_at`) group by date_format(created_at, \'%Y-%m-%d\'), date_format(created_at, \'%H\')');
        foreach ($logs as $log) {
            $rms[$log->date][(int)$log->name] = ['value' => $log->value];
        }

        return $this->response([
            'max' => $max,
            'dates' => $dates,
            'hours' => ['00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'],
            'pvs' => $pvs,
            'uvs' => $uvs,
            'ips' => $ips,
            'rms' => $rms,
        ]);
    }
}
