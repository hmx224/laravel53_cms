<?php

namespace App\Http\Requests;

class SiteRequest extends Request
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
            'title' => 'required',
            'domain' => 'required',
            'directory' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '请输入名称',
            'title.required' => '请输入标题',
            'domain.required' => '请输入域名',
            'directory.required' => '请输入发布目录',
        ];
    }
}
