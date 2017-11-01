<?php

namespace App\Http\Requests;

class SurveyRequest extends Request
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
            'item_subject.0' => 'required',
            'item_title1.0' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => '请填写调查标题',
            'begin_date.required' => '请选择调查开始日期',
            'end_date.required' => '请选择调查截止日期',
            'end_date.after' => '调查截止日期必须晚于调查开始日期',
            'item_subject.0.required' => '请填写问卷题目',
            'item_title1.0.required' => '请填写问卷选项'
        ];
    }
}