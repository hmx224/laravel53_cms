<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\PushLog;
use App\Models\User;
use Auth;
use Gate;
use JPush\Client as JPush;
use Request;
use Response;


class PushController extends BaseController
{
    public function log()
    {
        if (Gate::denies('@push')) {
            $this->middleware('deny403');
        }

        $users = User::pluck('name', 'id')
            ->toArray();
        //添加空选项
        array_unshift($users, '');

        return view('admin.logs.push', compact('users'));
    }

    public function logTable()
    {
        $filter = Request::all();

        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $logs = PushLog::with('user')
            ->owns()
            ->filter($filter)
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $total = PushLog::owns()
            ->filter($filter)
            ->count();

        $logs->transform(function ($log) {
            return [
                'id' => $log->id,
                'refer_id' => $log->refer_id,
                'refer_type' => $log->refer_type,
                'title' => $log->title,
                'send_no' => $log->send_no,
                'msg_id' => $log->msg_id,
                'username' => empty($log->user) ? '' : $log->user->name,
                'state_name' => $log->state_name,
                'state' => $log->state,
                'created_at' => $log->created_at->toDateTimeString(),
                'updated_at' => $log->updated_at->toDateTimeString(),
            ];
        });
        $ds = new \stdClass();
        $ds->total = $total;
        $ds->rows = $logs;

        return Response::json($ds);
    }

    public function send()
    {
        $input = Request::all();

        $module_id = $input['module_id'];
        $id = $input['push_id'];
        $title = $input['push_title'];

        $ios = $input['ios'];
        $android = $input['android'];
        $tag = $input['tag'];
        $alias = $input['alias'];

        //获取模块
        $module = Module::find($module_id);
        if (empty($module)) {
            return $this->responseError('此模块ID不存在');
        }

        //获取内容
        $content = call_user_func([$module->model_class, 'find'], $id);
        if (empty($content)) {
            \Session::flash('flash_warning', '无此记录');
            return redirect()->back();
        }

        //获取标题
        if (empty($title)) {
            $title = $content->title;
        }
        $title = mb_substr($title, 0, 40);

        $app_key = Auth::user()->site->jpush_app_key;
        $app_secret = Auth::user()->site->jpush_app_secret;
        $url = get_url('/api/' . $module->path . '/detail?id=' . $id);

        $extras = [
            'id' => $content->id,
            'type' => $module->singular,
            'title' => $content->title,
            'url' => $url,
        ];

        //设置日志
        $log = [
            'site_id' => $content->site_id,
            'refer_id' => $content->id,
            'refer_type' => $module->model_class,
            'title' => $title,
            'user_id' => Auth::user()->id,
            'url' => $url,
            'state' => PushLog::STATE_SUCCESS,
        ];

        //推送
        try {
            // 初始化
            $client = new JPush($app_key, $app_secret, storage_path('') . '/logs/jpush.log');

            $pusher = $client->push();
            if ($ios > 0 && $android > 0) {
                $pusher->setPlatform('all');
            } else if ($ios > 0) {
                $pusher->setPlatform('ios');
            } else if ($android > 0) {
                $pusher->setPlatform('android');
            }
            if (!empty($tag)) {
                $pusher->addAlias($tag);
            } else if (!empty($alias)) {
                $pusher->addAlias($alias);
            } else {
                $pusher->addAllAudience();
            }
            $pusher->setNotificationAlert($content->title);
            if ($android > 0) {
                $pusher->androidNotification($content->title, [
                    'extras' => $extras,
                ]);
            }
            if ($ios > 0) {
                $pusher->iosNotification($content->title, [
                    'title' => $title,
                    'sound' => 'default',
                    'badge' => 1,
                    'extras' => $extras,
                ]);
            }
            $pusher->options([
                'time_to_live' => 86400,
                'apns_production' => $ios == PushLog::IOS_PUSH_PRODUCTION,
            ]);
            $result = $pusher->send();
            \Log::debug('推送结果:' . json_encode($result));

            $log['send_no'] = $result['body']['sendno'];
            $log['msg_id'] = $result['body']['msg_id'];

            return Response::json([
                'status_code' => 200,
            ]);
        } catch (Exception $e) {
            $log['state'] = PushLog::STATE_FAILURE;
            $log['err_msg'] = $e->getMessage();
            \Log::debug('推送失败:' . json_encode($e));
            return '';
        } finally {
            PushLog::create($log);
        }
    }
}
