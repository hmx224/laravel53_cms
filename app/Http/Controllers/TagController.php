<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\User;
use DB;
use Gate;
use Request;
use Response;


class TagController extends BaseController
{
    public function index()
    {
        if (Gate::denies('@article')) {
            return abort(403);
        }

        $users = User::pluck('name', 'id')
            ->toArray();
        //添加空选项
        array_unshift($users, '');

        return view('admin.tags.index', compact('users'));
    }

    public function table()
    {
        $filters = Request::all();

        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $logs = Tag::with('refer.user')
            ->owns()
            ->filter($filters)
            ->orderBy('sort', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $total = Tag::owns()
            ->filter($filters)
            ->count();

        $logs->transform(function ($log) {
            return [
                'id' => $log->id,
                'refer_id' => $log->refer_id,
                'refer_type' => $log->refer_type,
                'title' => $log->refer->title,
                'clicks' => $log->refer->click_count,
                'username' => $log->refer->user->name,
                'created_at' => $log->created_at->toDateTimeString(),
                'updated_at' => $log->updated_at->toDateTimeString(),
            ];
        });
        $ds = new \stdClass();
        $ds->total = $total;
        $ds->rows = $logs;

        return Response::json($ds);
    }

    public function tree()
    {
        $tags = Tag::owns()
            ->select('name', DB::raw('count(*) as total'))
            ->groupBy('name')
            ->get();

        $tags = $tags->sortByDesc('total');

        $nodes = [];
        foreach ($tags as $tag) {
            $nodes[] = [
                'text' => $tag->name,
                'tags' => [$tag->total],
            ];
        }

        return Response::json($nodes);
    }

    public function sort()
    {
        return Tag::sort();
    }
}