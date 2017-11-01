<?php

namespace App\Http\Requests;

class VoteRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required',
            'begin_date' => 'required',
            'end_date' => 'required',
            'end_date' => 'required|after:begin_date',
            'multiple' => 'required',
            'item_title.0' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => '请填写投票标题',
            'begin_date.required' => '请选择投票开始日期',
            'end_date.required' => '请选择投票截止日期',
            'end_date.after' => '投票截止日期必须晚于投票开始日期',
            'multiple.required' => '请选择投票类型',
            'item_title.0.required' => '请填写投票选项'
        ];
    }
}