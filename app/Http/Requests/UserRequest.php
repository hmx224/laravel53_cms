<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'=>'required|unique:users,name',
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
            'site_ids.0' => 'required|exists:sites,id',
            'role_id'=>'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '姓名不能为空',
            'name.unique' => '姓名已存在',
            'username.required' => '用户名不能为空',
            'username.unique' => '用户名已存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码长度至少包含6个字符',
            'site_ids.0.required' => '请选择站点',
            'site_id.exists' => '站点ID不存在，请添加站点ID后再试',
            'role_id.required'=>'请选择角色',
        ];
    }
}
