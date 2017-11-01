<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class RoleRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'=>'required|unique:roles',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '请输入名称',
            'name.unique' => '此名称已存在',
        ];
    }
}
