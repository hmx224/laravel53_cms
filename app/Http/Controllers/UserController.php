<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use App\Models\UserLog;
use Auth;
use DB;
use Gate;
use Request;
use Response;

class UserController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        if (Gate::denies('@user')) {
            $this->middleware('deny403');
        }

        return view('admin.users.index');
    }

    public function create()
    {
        $roles = Role::pluck('name', 'id')->toArray();
        $sites = Site::all();

        return view('admin.users.create', compact('sites', 'roles'));
    }

    public function store(UserRequest $request)
    {
        $input = Request::all();

        $input['password'] = bcrypt($input['password']);
        $input['state'] = User::STATE_NORMAL;

        DB::beginTransaction();
        try {
            $user = User::create($input);

            //关联角色
            $user->roles()->sync([$input['role_id']]);

            //关联站点
            $user->sites()->sync($input['site_ids']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            DB::commit();
        }

        \Session::flash('flash_success', '添加成功');
        return redirect('/admin/users');
    }

    public function destroy($id)
    {
        $users = User::find($id);
        if ($users == null) {
            \Session::flash('flash_warning', '无此记录');
            return;
        }

        $users->state = User::STATE_CANCEL;
        $users->save();

        \Session::flash('flash_success', '注销成功');
    }

    public function edit($id)
    {
        $user = User::find($id);
        if ($user == null) {
            \Session::flash('flash_warning', '无此记录');
            return redirect('/admin/users');
        }

        $roles = Role::pluck('name', 'id')->toArray();
        $sites = Site::all();

        return view('admin.users.edit', compact('user', 'sites', 'roles'));
    }

    public function update($id, Request $request)
    {
        $user = User::find($id);

        if ($user == null) {
            \Session::flash('flash_warning', '无此记录');
            return redirect()->to($this->getRedirectUrl())
                ->withInput($request->input());
        }

        $input = Request::all();
        if (!empty($input['new_password'])) {
            $input['password'] = bcrypt($input['new_password']);
        }

        DB::beginTransaction();
        try {
            $user->update($input);

            //关联角色
            $user->roles()->sync([$input['role_id']]);

            //关联站点
            $user->sites()->sync($input['site_ids']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            DB::commit();
        }

        \Session::flash('flash_success', '修改成功!');
        return redirect('/admin/users');
    }

    function category($id)
    {
        $user = User::find($id);

        $category_ids = $user->categories->pluck('id')->toArray();
        return Response::json($category_ids);
    }

    public function tree($id)
    {
        return Response::json(User::getTree($id));
    }

    public function grant($id)
    {
        $user = User::find($id);

        $category_ids = Request::get('category_ids') ? Request::get('category_ids') : [];

        $user->categories()->sync($category_ids);

        \Session::flash('flash_success', '栏目查看更新成功!');
    }

    public function table()
    {
        $users = User::all();

        $users->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'password' => $user->password,
                'site_name' => implode(', ', $user->sites()->pluck('title')->toArray()),
                'state_name' => $user->stateName(),
                'role_name' => empty($user->roles()->first()) ? '' : $user->roles()->first()->name,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        $ds = new \stdClass();
        $ds->data = $users;

        return Response::json($ds);
    }

    public function log()
    {
        if (Gate::denies('@log')) {
            $this->middleware('deny403');
        }

        $users = User::pluck('name', 'id')
            ->toArray();
        //添加空选项
        array_unshift($users, '');

        return view('admin.logs.user', compact('users'));
    }

    public function logTable()
    {
        $filters = Request::all();

        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $logs = UserLog::with('site', 'user')
            ->filter($filters)
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $total = UserLog::filter($filters)
            ->count();

        $logs->transform(function ($log) {
            return [
                'id' => $log->id,
                'site_title' => empty($log->site) ? '' : $log->site->title,
                'action' => $log->action,
                'refer_id' => empty($log->refer_id) ? '' : $log->refer_id,
                'refer_type' => $log->refer_type,
                'ip' => $log->ip,
                'user_id' => $log->user_id,
                'user_name' => empty($log->user) ? '' : $log->user->name,
                'created_at' => $log->created_at->toDateTimeString(),
                'updated_at' => $log->updated_at->toDateTimeString(),
            ];
        });
        $ds = new \stdClass();
        $ds->total = $total;
        $ds->rows = $logs;

        return Response::json($ds);
    }

    public function changePasswordForm()
    {
        return view('admin.auth.passwords.change');
    }

    public function changePassword()
    {
        $user = User::find(Auth::user()->id);

        if ($user == null) {
            return ('<script>alert("无该条记录!");window.location.href="/password/change"</script>;');
        }

        $input = Request::all();

        if (!password_verify($input['old'], $user->password)) {
            return ('<script>alert("旧密码输入错误!");window.location.href="/password/change"</script>;');
        }

        if ($input['new'] != $input['confirm']) {
            return ('<script>alert("两次输入的密码不一致!");window.location.href="/password/change"</script>;');
        }

        $user->password = bcrypt($input['new']);

        $user->save();

        return ('<script>alert("修改成功!");window.location.href="/"</script>;');
    }
}
