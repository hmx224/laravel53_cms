<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Gate;
use Request;

class ProfileController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $user = Auth::user();
        return view('admin.users.profile', compact('user'));
    }

    public function update($id, Request $request)
    {
        $user = User::find($id);

        if ($user == null) {
            \Session::flash('flash_warning', '无此记录');
            return redirect('/admin/users');
        }

        $input = Request::all();
        if ($input['name'] == null) {
            \Session::flash('flash_warning', '姓名不能为空!');
            return redirect('/admin/profiles');
        }

        if ($input['new'] != $input['pwdConfirm']) {
            \Session::flash('flash_warning', '两次输入的密码不一致!');
            return redirect('/admin/profiles');
        }

        if ($input['new'] != null && $input['pwdConfirm'] != null) {
            $user->password = bcrypt($input['new']);
        }

        $user->update($input);

        \Session::flash('flash_success', '保存成功!');
        return redirect('/admin/profiles');
    }

}
