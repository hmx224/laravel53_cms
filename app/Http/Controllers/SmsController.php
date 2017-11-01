<?php

namespace App\Http\Controllers;

use App\Models\SmsLog;
use Gate;
use Request;
use Response;


class SmsController extends Controller
{
    public function log()
    {
        if (Gate::denies('@log')) {
            $this->middleware('deny403');
        }
        return view('admin.logs.sms');
    }

    public function logTable()
    {
        $filters = Request::all();

        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $logs = SmsLog::with('site')
            ->owns()
            ->filter($filters)
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $total = SmsLog::owns()
            ->filter($filters)
            ->count();

        $logs->transform(function ($log) {
            return [
                'id' => $log->id,
                'site_title' => $log->site->title,
                'mobile' => $log->mobile,
                'message' => $log->message,
                'state_name' => $log->stateName(),
                'created_at' => $log->created_at->toDateTimeString(),
                'updated_at' => $log->updated_at->toDateTimeString(),
            ];
        });
        $ds = new \stdClass();
        $ds->total = $total;
        $ds->rows = $logs;

        return Response::json($ds);
    }
}

?>