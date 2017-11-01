<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleField extends Model
{
    const TYPE_TEXT = 1;
    const TYPE_LONG_TEXT = 2;
    const TYPE_INTEGER = 3;
    const TYPE_FLOAT = 4;
    const TYPE_DATETIME = 5;
    const TYPE_HTML = 6;
    const TYPE_ENTITY = 7;
    const TYPE_IMAGE = 8;
    const TYPE_AUDIO = 9;
    const TYPE_VIDEO = 10;
    const TYPE_IMAGES = 11;
    const TYPE_AUDIOS = 12;
    const TYPE_VIDEOS = 13;
    const TYPE_TAGS = 14;

    const TYPES = [
        1 => '文本',
        2 => '长文本',
        3 => '整数',
        4 => '浮点数',
        5 => '日期时间',
        6 => 'HTML',
        7 => '实体引用',
        8 => '图片',
        9 => '音频',
        10 => '视频',
        11 => '图片集',
        12 => '音频集',
        13 => '视频集',
        14 => '标签',
    ];

    const EDITOR_TYPE_TEXT = 1;
    const EDITOR_TYPE_TEXTAREA = 2;
    const EDITOR_TYPE_SELECT_SINGLE = 3;
    const EDITOR_TYPE_SELECT_MULTI = 4;
    const EDITOR_TYPE_DATETIME = 5;
    const EDITOR_TYPE_HTML = 6;
    const EDITOR_TYPE_ENTITY = 7;
    const EDITOR_TYPE_IMAGE = 8;
    const EDITOR_TYPE_AUDIO = 9;
    const EDITOR_TYPE_VIDEO = 10;
    const EDITOR_TYPE_IMAGES = 11;
    const EDITOR_TYPE_AUDIOS = 12;
    const EDITOR_TYPE_VIDEOS = 13;
    const EDITOR_TYPE_TAGS = 14;

    const EDITOR_TYPES = [
        1 => '文本',
        2 => '多行文本',
        3 => '单选',
        4 => '多选',
        5 => '日期时间',
        6 => '富文本',
        7 => '实体',
        8 => '图片',
        9 => '音频',
        10 => '视频',
        11 => '图片集',
        12 => '音频集',
        13 => '视频集',
        14 => '标签',
    ];

    const COLUMN_ALIGN_LEFT = 1;
    const COLUMN_ALIGN_CENTER = 2;
    const COLUMN_ALIGN_RIGHT = 3;

    const COLUMN_ALIGNS = [
        1 => '左',
        2 => '中',
        3 => '右',
    ];

    protected $fillable = [
        'module_id',
        'name',
        'title',
        'label',
        'type',
        'default',
        'required',
        'unique',
        'min_length',
        'max_length',
        'system',
        'index',
        'column_show',
        'column_align',
        'column_width',
        'column_editable',
        'column_formatter',
        'column_index',
        'editor_show',
        'editor_type',
        'editor_options',
        'editor_columns',
        'editor_rows',
        'editor_readonly',
        'editor_group',
        'editor_index',
    ];

    public function typeName()
    {
        return array_key_exists($this->type, static::TYPES) ? static::TYPES[$this->type] : '';
    }

    public function editorTypeName()
    {
        return array_key_exists($this->editor_type, static::EDITOR_TYPES) ? static::EDITOR_TYPES[$this->editor_type] : '';
    }

    public function columnAlignName()
    {
        return array_key_exists($this->column_align, static::COLUMN_ALIGNS) ? static::COLUMN_ALIGNS[$this->column_align] : '';
    }

    public static function stores($input)
    {
        //设置序号+1
        if (isset($input['index']) && intval($input['index']) == 0) {
            $input['index'] = ModuleField::where('module_id', $input['module_id'])->where('index', '<', '90')->max('index') + 1;
        }

        $count = ModuleField::where('name', $input['name'])
            ->where('module_id', $input['module_id'])
            ->count();

        if ($count > 0) {
            \Session::flash('flash_warning', '字段已存在');
            return false;
        }

        self::create($input);

        \Session::flash('flash_success', '添加成功');
        return true;
    }

    public static function updates($id, $input)
    {
        $field = self::find($id);

        if ($field == null) {
            \Session::flash('flash_warning', '无此记录');
            return false;
        }

        $field->update($input);

        \Session::flash('flash_success', '修改成功');
        return true;
    }
}
