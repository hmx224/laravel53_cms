<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class DictionaryRequest extends Request
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
            'code'=>'required',
            'name'=> 'required',
        ];
    }
    public function messages()
    {
        return [
            'code.required' => '请填写编码',
            'name.required' => '请填写名称',
        ];
    }
}
