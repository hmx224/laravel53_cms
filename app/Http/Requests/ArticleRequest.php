<?php

namespace App\Http\Requests;

class ArticleRequest extends Request
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
            'title' => 'required',
            'category_id' => 'exists:categories,id',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => '请填写标题',
            'category_id.exists' => '请选择栏目',
        ];
    }
}
