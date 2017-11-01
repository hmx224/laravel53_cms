<?php

namespace App\Http\Requests;

class MenuRequest extends Request
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
            'name' => 'required',
            'icon' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '请输入名称',
            'url.required' => '请输入URL',
            'icon.required' => '请选择图标',
        ];
    }
}
