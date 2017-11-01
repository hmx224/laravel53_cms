<?php

namespace App\Http\Requests;

class ContentRequest extends Request
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
            'title'=>'required',
            'start_time' => 'sometimes|required',
            'end_time' => 'sometimes|required|after:start_time',
        ];
    }

    public function messages()
    {
        return [
            'title.required'      => '请填写标题',
            'start_time.required' => '请选择开始时间',
            'end_time.required' => '请选择结束时间',
            'end_time.after' => '开始时间不得大于结束时间',
        ];
    }
}
