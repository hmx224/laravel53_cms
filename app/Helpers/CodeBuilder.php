<?php

namespace App\Helpers;

use App\Models\ModuleField;

class CodeBuilder
{
    private $module;
    private $stub_path;

    public function __construct($module)
    {
        $this->module = $module;

        //判断此模块是否有栏目字段
        if ($this->module->fields()->where('name', 'category_id')->count() > 0) {
            $this->stub_path = __DIR__ . '/stubs/2/';
        } else {
            $this->stub_path = __DIR__ . '/stubs/1/';
        }
    }

    public function replace($content)
    {
        $content = str_replace('__module_name__', $this->module->name, $content);
        $content = str_replace('__module_title__', $this->module->title, $content);
        $content = str_replace('__module_path__', $this->module->path, $content);
        $content = str_replace('__singular__', $this->module->singular, $content);
        $content = str_replace('__plural__', $this->module->plural, $content);
        $content = str_replace('__table__', $this->module->table_name, $content);
        $content = str_replace('__model__', $this->module->model_name, $content);
        $content = str_replace('__controller__', $this->module->controller_name, $content);
        $content = str_replace('__permission__', $this->module->singular, $content);

        return $content;
    }

    public function createModel()
    {
        $content = file_get_contents($this->stub_path . 'model.php');

        $content = static::replace($content);

        $fillable = [];
        $dates = [];
        $entities = [];
        foreach ($this->module->fields()->orderBy('index')->get() as $field) {
            if (in_array($field->name, ['id', 'tags', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }
            //日期
            if ($field->type == ModuleField::TYPE_DATETIME) {
                $dates[] = '\'' . $field->name . '\'';
            }
            //实体
            if ($field->type == ModuleField::TYPE_ENTITY) {
                $entities[] = '\'' . $field->name . '\'';
            }
            //填充
            if (!in_array($field->type, [ModuleField::TYPE_IMAGES, ModuleField::TYPE_AUDIOS, ModuleField::TYPE_VIDEOS, ModuleField::TYPE_TAGS])) {
                $fillable[] = '\'' . $field->name . '\'';
            }
        }

        $content = str_replace('__fillable__', implode(', ', $fillable), $content);
        $content = str_replace('__dates__', implode(', ', $dates), $content);
        $content = str_replace('__entities__', implode(', ', $entities), $content);

        //创建模型目录
        $path = base_path('modules/' . $this->module->singular . '/models/');
        @mkdir($path, 0755, true);

        file_put_contents($path . $this->module->model_name . '.php', $content);
    }

    public function createController()
    {
        $content = file_get_contents($this->stub_path . 'controller.php');

        $content = static::replace($content);

        //创建控制器目录
        $path = base_path('modules/' . $this->module->singular . '/web/');
        @mkdir($path, 0755, true);

        file_put_contents($path . $this->module->controller_name . '.php', $content);
    }

    public function createApi()
    {
        $content = file_get_contents($this->stub_path . 'api.php');

        $content = static::replace($content);

        //创建API目录
        $path = base_path('modules/' . $this->module->singular . '/api/');
        @mkdir($path, 0755, true);

        file_put_contents($path . $this->module->controller_name . '.php', $content);
    }

    public function createViews()
    {
        //创建视图目录
        $path = base_path('modules/' . $this->module->singular . '/views/');
        @mkdir($path, 0755, true);

        //index.php
        $content = file_get_contents($this->stub_path . 'views/index.blade.php');

        $content = static::replace($content);

        file_put_contents($path . '/index.blade.php', $content);

        //query.php
        $content = file_get_contents($this->stub_path . 'views/query.blade.php');

        $content = static::replace($content);

        file_put_contents($path . '/query.blade.php', $content);

        //script.php
        $content = file_get_contents($this->stub_path . 'views/script.blade.php');

        $content = static::replace($content);

        file_put_contents($path . '/script.blade.php', $content);

        //toolbar.php
        $content = file_get_contents($this->stub_path . 'views/toolbar.blade.php');

        $content = static::replace($content);

        file_put_contents($path . '/toolbar.blade.php', $content);
    }

    public function appendRoutes()
    {
        //创建路由目录
        $path = base_path('modules/' . $this->module->singular . '/routes/');
        @mkdir($path, 0755, true);

        //web.php
        $content = file_get_contents($this->stub_path . 'routes/web.php');

        $content = static::replace($content);

        file_put_contents($path . '/web.php', $content);

        //admin.php
        $content = file_get_contents($this->stub_path . 'routes/admin.php');

        $content = static::replace($content);

        file_put_contents($path . '/admin.php', $content);

        //api.php
        $content = file_get_contents($this->stub_path . 'routes/api.php');

        $content = static::replace($content);

        file_put_contents($path . '/api.php', $content);
    }
}
