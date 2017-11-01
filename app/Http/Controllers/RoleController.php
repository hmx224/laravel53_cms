<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use Gate;
use Request;
use Response;

class RoleController extends Controller
{
    public function __construct()
    {
    }

    public function transform($permission)
    {
        $attributes = $permission->getAttributes();

        $attributes['id'] = $permission->id;
        $attributes['description'] = $permission->description;
        $attributes['group'] = $permission->group;

        return $attributes;
    }

    public function index()
    {
        if (Gate::denies('@role')) {
            $this->middleware('deny403');
        }

        return view('admin.roles.index');
    }

    public function create()
    {
        $permissions = Permission::orderBy('group')->orderBy('id', 'asc')->get();
        $count = Permission::count();

        $permissions = $permissions->transform(function ($permission) {
            return $this->transform($permission);
        });

        return view('admin.roles.create', compact('permissions', 'count'));
    }

    public function store(RoleRequest $request)
    {
        $input = Request::all();
        $role = Role::create($input);

        if (array_key_exists('permission_id', $input)) {
            foreach ($input['permission_id'] as $permission_id) {
                PermissionRole::create([
                    'permission_id' => $permission_id,
                    'role_id' => $role->id,
                ]);
            }
        }

        \Session::flash('flash_success', '添加成功');
        return redirect('/admin/roles');
    }

    public function destroy($id)
    {
        $roles = Role::find($id);
        if ($roles == null) {
            \Session::flash('flash_warning', '无此记录');
            return;
        }

        $roles->delete();

        //删除对应permission_role表里面的值
        PermissionRole::where('role_id', $id)->delete();
        \Session::flash('flash_success', '删除成功');
    }

    public function edit($id)
    {
        $role = Role::with('perms')->find($id);
        if (empty($role)) {
            \Session::flash('flash_warning', '无此记录');
            return redirect('/admin/roles');
        }

        $permissions = Permission::orderBy('group')->orderBy('id', 'asc')->get();
        $count = Permission::count();
        $permissions = $permissions->transform(function ($permission) {
            return $this->transform($permission);
        });

        $perms = PermissionRole::where('role_id', $id)
            ->pluck('permission_id')
            ->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'perms', 'count'));
    }

    public function update($id, Request $request)
    {
        $input = Request::all();

        $role = Role::find($id);

        $role->update($input);

        //删除之前所选
        PermissionRole::where('role_id', $id)->delete();
        if (array_key_exists('permission_id', $input))
            foreach ($input['permission_id'] as $permission_id) {
                PermissionRole::create([
                    'permission_id' => $permission_id,
                    'role_id' => $id,
                ]);
            }
        \Session::flash('flash_success', '修改成功');
        return redirect('/admin/roles');
    }

    public function table()
    {
        $roles = Role::all();
        $names = User::getNames();

        foreach($roles as $role){
            foreach($role->users as $user){
                $role->role_users .= $names[$user->pivot->user_id].'　';
            }
        }
        $roles->transform(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'role_users' => $role->role_users,
            ];
        });

        $ds = new \stdClass();
        $ds->data = $roles;

        return Response::json($ds);
    }
}
