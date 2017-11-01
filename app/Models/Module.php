<?php

namespace App\Models;

use App\Console\Commands\SwaggerCommand;
use App\Helpers\CodeBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Request;
use Response;
use Schema;
use Validator;

class Module extends Model
{
    const STATE_DISABLE = 0;
    const STATE_ENABLE = 1;

    const STATES = [
        0 => '已禁用',
        1 => '已启用',
    ];

    const SORT_TYPES = [
        0 => '栏目序号',
        1 => '创建日期',
    ];

    const SORT_DIRECTIONS = [
        0 => '正序',
        1 => '倒序',
    ];

    protected $fillable = [
        'name',
        'title',
        'table_name',
        'icon',
        'groups',
        'is_lock',
        'use_category',
        'state',
    ];

    public function getModelNameAttribute()
    {
        return $this->name;
    }

    public function getModelClassAttribute()
    {
        return 'Modules\\' . ucfirst($this->name) . '\\Models\\' . ucfirst($this->name);
    }

    public function getControllerNameAttribute()
    {
        return $this->name . 'Controller';
    }

    public function getPluralAttribute()
    {
        return str_plural(strtolower($this->name));
    }

    public function getSingularAttribute()
    {
        return str_singular(strtolower($this->name));
    }

    public function getPathAttribute()
    {
        return strtolower($this->name);
    }

    public function fields()
    {
        return $this->hasMany(ModuleField::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function stateName()
    {
        return array_key_exists($this->state, static::STATES) ? static::STATES[$this->state] : '';
    }

    public static function findByName($name)
    {
        //TODO 改成从缓存获取
        return self::whereRaw("lower(name) = '$name'")->first();
    }

    public static function findByPath($path)
    {
        $key = 'module-all';
        $modules = cache_remember($key, 1, function () {
            return Module::where('state', Module::STATE_ENABLE)
                ->get();
        });

        $modules = $modules->filter(function ($module) use ($path) {
            return strtolower($module->name) == strtolower($path);
        });
        if (count($modules) == 0) {
            return null;
        }
        return $modules->first();
    }

    public static function stores($input)
    {
        $input['state'] = self::STATE_ENABLE;

        $module = self::create($input);

        $module->fields()->create([
            'name' => 'id',
            'title' => 'ID',
            'label' => 'ID',
            'type' => ModuleField::TYPE_INTEGER,
            'system' => 1,
            'index' => 1,
            'column_show' => 1,
            'column_align' => ModuleField::COLUMN_ALIGN_LEFT,
            'column_width' => 45,
            'column_index' => 1,
        ]);

        $module->fields()->create([
            'name' => 'site_id',
            'title' => '站点ID',
            'label' => '站点',
            'type' => ModuleField::TYPE_INTEGER,
            'system' => 1,
            'index' => 2,
        ]);

        if ($input['use_category']) {
            $module->fields()->create([
                'name' => 'category_id',
                'title' => '栏目ID',
                'label' => '栏目',
                'type' => ModuleField::TYPE_INTEGER,
                'system' => 1,
                'index' => 3,
            ]);

            $module->fields()->create([
                'name' => 'title',
                'title' => '标题',
                'label' => '标题',
                'type' => ModuleField::TYPE_TEXT,
                'system' => 1,
                'index' => 4,
                'column_show' => 1,
                'column_align' => ModuleField::COLUMN_ALIGN_LEFT,
                'column_index' => 2,
                'editor_show' => 1,
                'editor_type' => ModuleField::EDITOR_TYPE_TEXT,
                'editor_columns' => 11,
                'editor_group' => '基本信息',
                'editor_index' => 1,
            ]);

        } else {
            $module->fields()->create([
                'name' => 'title',
                'title' => '标题',
                'label' => '标题',
                'type' => ModuleField::TYPE_TEXT,
                'system' => 1,
                'index' => 3,
                'column_show' => 1,
                'column_align' => ModuleField::COLUMN_ALIGN_LEFT,
                'column_index' => 2,
                'editor_show' => 1,
                'editor_type' => ModuleField::EDITOR_TYPE_TEXT,
                'editor_columns' => 11,
                'editor_group' => '基本信息',
                'editor_index' => 1,
            ]);
        }

        $module->fields()->create([
            'name' => 'member_id',
            'title' => '会员ID',
            'label' => '会员',
            'type' => ModuleField::TYPE_ENTITY,
            'system' => 1,
            'index' => 91,
        ]);

        $module->fields()->create([
            'name' => 'user_id',
            'title' => '用户ID',
            'label' => '用户',
            'type' => ModuleField::TYPE_ENTITY,
            'system' => 1,
            'index' => 92,
        ]);

        $module->fields()->create([
            'name' => 'sort',
            'title' => '序号',
            'label' => '序号',
            'type' => ModuleField::TYPE_INTEGER,
            'system' => 1,
            'index' => 93,
        ]);

        $module->fields()->create([
            'name' => 'state',
            'title' => '状态',
            'label' => '状态',
            'type' => ModuleField::TYPE_INTEGER,
            'system' => 1,
            'index' => 94,
            'column_show' => 1,
            'column_align' => ModuleField::COLUMN_ALIGN_CENTER,
            'column_width' => 45,
            'column_formatter' => 'stateFormatter',
            'column_index' => 9,
        ]);

        $module->fields()->create([
            'name' => 'created_at',
            'title' => '创建时间',
            'label' => '创建时间',
            'type' => ModuleField::TYPE_DATETIME,
            'system' => 1,
            'index' => 95,
        ]);

        $module->fields()->create([
            'name' => 'updated_at',
            'title' => '修改时间',
            'label' => '修改时间',
            'type' => ModuleField::TYPE_DATETIME,
            'system' => 1,
            'index' => 96,
        ]);

        $module->fields()->create([
            'name' => 'deleted_at',
            'title' => '删除时间',
            'label' => '删除时间',
            'type' => ModuleField::TYPE_DATETIME,
            'system' => 1,
            'index' => 97,
        ]);

        $module->fields()->create([
            'name' => 'published_at',
            'title' => '发布时间',
            'label' => '发布时间',
            'type' => ModuleField::TYPE_DATETIME,
            'system' => 1,
            'index' => 98,
        ]);

        \Session::flash('flash_success', '添加成功');
        return true;
    }

    public static function copy($input)
    {
        $input['state'] = self::STATE_ENABLE;

        $module = self::create($input);

        $moduleFields = ModuleField::where('module_id', $input['module_id'])->get();

        foreach ($moduleFields as $moduleField) {
            $input = $moduleField->attributes;
            $module->fields()->create($input);
        }

        \Session::flash('flash_success', '复制成功');
        return true;
    }

    public static function updates($id, $input)
    {
        $module = self::find($id);

        if ($module == null) {
            \Session::flash('flash_warning', '无此记录');
            return false;
        }
        $module->update($input);

        \Session::flash('flash_success', '修改成功');
        return true;
    }

    public static function table()
    {
        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $ds = new \stdClass();
        $modules = static::orderBy('id')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $ds->total = static::count();

        $modules->transform(function ($module) {
            $attributes = $module->getAttributes();

            foreach ($module->dates as $date) {
                $attributes[$date] = empty($module->$date) ? '' : $module->$date->toDateTimeString();
            }
            $attributes['state_name'] = $module->stateName();
            $attributes['created_at'] = empty($module->created_at) ? '' : $module->created_at->toDateTimeString();
            $attributes['updated_at'] = empty($module->updated_at) ? '' : $module->updated_at->toDateTimeString();

            return $attributes;
        });

        $ds->data = $modules;

        return Response::json($ds);
    }

    /**
     * 转换结构
     *
     * @param $id
     * @return array|mixed
     */
    public static function transform($id)
    {
        $module = Module::find($id);
        $module = [
            'id' => $module->id,
            'name' => $module->name,
            'title' => $module->title,
            'table_name' => $module->table_name,
            'model_class' => $module->model_class,
            'fa_icon' => $module->fa_icon,
            'groups' => explode(',', $module->groups),
            'categories' => $module->categories()->orderBy('sort')->get(),
            'columns' => $module->fields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'title' => $field->title,
                    'label' => $field->label,
                    'type' => $field->type,
                    'show' => $field->column_show,
                    'align' => $field->column_align,
                    'width' => $field->column_width,
                    'editable' => $field->column_editable,
                    'formatter' => $field->column_formatter,
                    'index' => $field->column_index,

                ];
            }),
            'editors' => $module->fields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'title' => $field->title,
                    'label' => $field->label,
                    'type' => $field->editor_type,
                    'show' => $field->editor_show,
                    'options' => $field->editor_options,
                    'columns' => $field->editor_columns,
                    'rows' => $field->editor_rows,
                    'required' => $field->required,
                    'readonly' => $field->editor_readonly,
                    'group' => $field->editor_group,
                    'index' => $field->editor_index,
                ];
            }),
            'fields' => $module->fields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'title' => $field->title,
                    'label' => $field->label,
                    'type' => $field->type,
                    'default' => $field->default,
                    'required' => $field->required,
                    'unique' => $field->unique,
                    'min_length' => $field->min_length,
                    'max_length' => $field->max_length,
                    'system' => $field->system,
                    'index' => $field->index,
                    'column' => [
                        'name' => $field->name,
                        'title' => $field->title,
                        'label' => $field->label,
                        'type' => $field->type,
                        'show' => $field->column_show,
                        'align' => $field->column_align,
                        'width' => $field->column_width,
                        'editable' => $field->column_editable,
                        'formatter' => $field->column_formatter,
                        'index' => $field->column_index,

                    ],
                    'editor' => [
                        'name' => $field->name,
                        'title' => $field->title,
                        'label' => $field->label,
                        'type' => $field->editor_type,
                        'show' => $field->editor_show,
                        'options' => $field->editor_options,
                        'columns' => $field->editor_columns,
                        'rows' => $field->editor_rows,
                        'required' => $field->required,
                        'readonly' => $field->editor_readonly,
                        'group' => $field->editor_group,
                        'index' => $field->editor_index,
                    ]
                ];
            }),
        ];

        $module = json_decode(json_encode($module));

        //数组转对象数组
        $groups = [];
        foreach ($module->groups as $group) {
            $groups[] = (object)['name' => $group];
        }

        $module->groups = $groups;

        //编辑器分组
        foreach ($module->groups as $group) {
            //过滤
            $group->editors = array_filter($module->editors, function ($editor) use ($group) {
                return $editor->show && $editor->group == $group->name;
            });

            //分组排序
            $group->editors = array_values(array_sort($group->editors, function ($editor) {
                return $editor->index;
            }));
        }

        //表格列过滤
        $module->columns = array_filter($module->columns, function ($column) {
            return $column->show;
        });

        //表格列排序
        $module->columns = array_values(array_sort($module->columns, function ($column) {
            return $column->index;
        }));

        return $module;
    }

    /**
     * 生成数据结构
     * @param $module
     */
    public static function migrate($module)
    {
        //判断权限是否已存在
        if (!Permission::where('name', '@' . $module->singular)->exists()) {
            //添加权限
            Permission::insert([
                ['name' => '@' . $module->singular, 'description' => $module->title, 'sort' => '1'],
                ['name' => '@' . $module->singular . '-create', 'description' => $module->title . '-添加', 'sort' => '2'],
                ['name' => '@' . $module->singular . '-edit', 'description' => $module->title . '-编辑', 'sort' => '3'],
                ['name' => '@' . $module->singular . '-delete', 'description' => $module->title . '-删除', 'sort' => '4'],
                ['name' => '@' . $module->singular . '-publish', 'description' => $module->title . '-发布', 'sort' => '5'],
                ['name' => '@' . $module->singular . '-cancel', 'description' => $module->title . '-撤回', 'sort' => '6'],
                ['name' => '@' . $module->singular . '-sort', 'description' => $module->title . '-排序', 'sort' => '7'],
            ]);

            if (!Schema::hasTable($module->table_name)) {
                Schema::create($module->table_name, function (Blueprint $table) {
                    $table->increments('id');
                    $table->timestamps();
                });
            }
        }

        //删除字段
        $old_fields = Schema::getColumnListing($module->table_name);

        $new_fields = [];
        foreach ($module->fields as $field) {
            $new_fields[] = $field->name;
        }
        $fields = array_diff($old_fields, $new_fields);
        foreach ($fields as $field) {
            Schema::table($module->table_name, function (Blueprint $table) use ($field) {
                $table->dropColumn($field);
            });
        }

        $fields = $module->fields()->orderBy('index')->get();
        foreach ($fields as $key => $field) {
            if ($key == 0) {
                continue;
            } else {
                $previous = $fields[$key - 1];
            }
            if (Schema::hasColumn($module->table_name, $field->name)) {
                //修改字段
                Schema::table($module->table_name, function (Blueprint $table) use ($field, $previous) {
                    switch ($field->type) {
                        case ModuleField::TYPE_INTEGER:
                        case ModuleField::TYPE_ENTITY:
                            $table->integer($field->name)->after($previous->name)->change();
                            break;
                        case ModuleField::TYPE_TEXT:
                        case ModuleField::TYPE_IMAGE:
                        case ModuleField::TYPE_AUDIO:
                        case ModuleField::TYPE_VIDEO:
                        case ModuleField::TYPE_IMAGES:
                        case ModuleField::TYPE_AUDIOS:
                        case ModuleField::TYPE_VIDEOS:
                            $table->text($field->name)->after($previous->name)->change();
                            break;
                        case ModuleField::TYPE_LONG_TEXT:
                        case ModuleField::TYPE_HTML:
                            $table->longText($field->name)->after($previous->name)->change();
                            break;
                        case ModuleField::TYPE_FLOAT:
                            $table->float($field->name)->after($previous->name)->change();
                            break;
                        case ModuleField::TYPE_DATETIME:
                            $table->datetime($field->name)->nullable()->after($previous->name)->change();
                            break;
                    }
                });
            } else {
                //新增字段
                Schema::table($module->table_name, function (Blueprint $table) use ($field, $previous) {
                    switch ($field->type) {
                        case ModuleField::TYPE_INTEGER:
                        case ModuleField::TYPE_ENTITY:
                            $table->integer($field->name)->after($previous->name)->comment($field->title);
                            break;
                        case  ModuleField::TYPE_TEXT:
                        case ModuleField::TYPE_IMAGE:
                        case ModuleField::TYPE_AUDIO:
                        case ModuleField::TYPE_VIDEO:
                        case ModuleField::TYPE_IMAGES:
                        case ModuleField::TYPE_AUDIOS:
                        case ModuleField::TYPE_VIDEOS:
                            $table->text($field->name)->after($previous->name)->comment($field->title);
                            break;
                        case ModuleField::TYPE_LONG_TEXT:
                        case ModuleField::TYPE_HTML:
                            $table->text($field->name)->after($previous->name)->comment($field->title);
                            break;
                        case ModuleField::TYPE_FLOAT:
                            $table->float($field->name)->after($previous->name)->comment($field->title);
                            break;
                        case ModuleField::TYPE_DATETIME:
                            $table->datetime($field->name)->nullable()->after($previous->name)->comment($field->title);
                            break;
                    }
                });
            }
        }
    }

    /**
     * 生成模块代码和API文档
     *
     * @param $module
     */
    public static function generate($module)
    {
        //代码生成
        $builder = new CodeBuilder($module);
        //生成model
        $builder->createModel();

        //生成controller
        $builder->createController();

        //生成api
        $builder->createApi();

        //生成view
        $builder->createViews();

        //生成route
        $builder->appendRoutes();

        //生成api文档
        $swagger = new SwaggerCommand();
        $swagger->handle();
    }

    /**
     * 验证
     *
     * @param $module
     * @param $input
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($module, $input)
    {
        $rules = [];
        $messages = [];
        foreach ($module->fields as $field) {
            $rule = '';
            if ($field->required) {
                $rule .= 'required|';
                $messages[$field->name . '.required'] = ($field->type == ModuleField::TYPE_ENTITY ? '请选择' : '请输入') . $field->label;
            }
            if ($field->min_length > 0) {
                $rule .= 'min:' . $field->min_length . '|';
                $messages[$field->name . '.min'] = $field->label . '至少' . $field->min_length . '个字符';
            }
            if ($field->max_length > 0) {
                $rule .= 'max:' . $field->max_length . '|';
                $messages[$field->name . '.max'] = $field->label . '最多' . $field->max_length . '个字符';
            }
            if ($field->unique) {
                $rule .= 'unique:' . $module->table_name . '|';
                $messages[$field->name . '.unique'] = $field->label . '已存在';
            }
            if ($rule != "") {
                $rules[$field->name] = trim($rule, '|');
            }
        }

        return Validator::make($input, $rules, $messages);
    }
}
