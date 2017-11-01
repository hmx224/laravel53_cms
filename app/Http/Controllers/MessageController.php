<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Request;
use Response;
use Gate;

class MessageController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        if (Gate::denies('@message')) {
            $this->middleware('deny403');
        }

        return view('admin.messages.index');
    }

    public function update($id)
    {
        $message = Message::find($id);

        if ($message == null) {
            return;
        }

        $message->update(Request::all());
    }

    public function destroy($id)
    {
        if (Gate::denies('@message-delete')) {
            \Session::flash('flash_warning', '无此操作权限');
            return;
        }

        $message = Message::find($id);
        $message->state = Message::STATE_DELETED;
        $message->save();
        \Session::flash('flash_success', '删除成功');
    }

    public function pass($id)
    {
        if (Gate::denies('@message-pass')) {
            \Session::flash('flash_warning', '无此操作权限');
            return;
        }

        $message = Message::find($id);
        $message->state = Message::STATE_PASSED;
        $message->save();
        \Session::flash('flash_success', '审核成功');
    }

    public function table()
    {
        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;
        $state = Request::get('state');
        $id = Request::get('id');

        if (empty($state) && $state == '') {
            $messages = Message::owns()
                ->where('member_id',$id)
                ->orderBy('id', 'desc')
                ->skip($offset)
                ->limit($limit)
                ->get();

            $total = Message::owns()
                ->where('member_id',$id)
                ->count();
        } else {
            $messages = Message::owns()
                ->where('member_id',$id)
                ->where('state', $state)
                ->orderBy('id', 'desc')
                ->skip($offset)
                ->limit($limit)
                ->get();

            $total = Message::owns()
                ->where('member_id',$id)
                ->where('state', $state)
                ->count();
        }

        $messages->transform(function ($message) {
            return [
                'id' => $message->id,
                'type' => $message->type,
                'type_name' => $message->typeName(),
                'title' => $message->title,
                'content' => $message->content,
                'member_id' => $message->member_id,
                'state' => $message->state,
                'state_name' => $message->stateName(),
                'created_at' => empty($message->created_at) ? '' : $message->created_at->toDateTimeString(),
                'updated_at' => empty($message->updated_at) ? '' : $message->updated_at->toDateTimeString(),
            ];
        });

        $ds = new \stdClass();
        $ds->total = $total;
        $ds->rows = $messages;

        return Response::json($ds);
    }

    public function state($state)
    {
        $ids = Request::get('ids');

        switch ($state) {
            case Message::STATE_PASSED:
                if (Gate::denies('@message-pass')) {
                    \Session::flash('flash_warning', '无此操作权限');
                    return;
                }
                $state_name = '已审核';
                break;
            case Message::STATE_DELETED:
                if (Gate::denies('@message-delete')) {
                    \Session::flash('flash_warning', '无此操作权限');
                    return;
                }
                $state_name = '删除';
                break;
            default:
                \Session::flash('flash_warning', '操作错误!');
                return;
        }

        foreach ($ids as $id) {
            $article = Message::find($id);

            if ($article == null) {
                \Session::flash('flash_warning', '无此记录!');
                return;
            }

            $article->state = $state;
            $article->save();
        }

        \Session::flash('flash_success', $state_name . '成功!');
    }
}
