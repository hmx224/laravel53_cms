<ul id="tabs" class="nav nav-tabs">
    <li>
        <a href="/admin/modules" data-toggle="tooltip" data-placement="right" title="返回"> <i class="fa fa-chevron-left"></i> 返回</a>
    </li>
    <li class="active">
        <a href="#tab_field" data-toggle="tab"><i class="fa fa-bars"></i> 字段</a>
    </li>
    <li>
        <a href="#tab_column" data-toggle="tab"><i class="fa fa-table"></i> 表格</a>
    </li>
    <li>
        <a href="#tab_editor" data-toggle="tab"><i class="fa fa-pencil"></i> 表单</a>
    </li>
    <li class="pull-right">
        <button class="btn btn-info btn-xs margin-r-5 margin-t-5" id="btn_create" data-toggle="modal" data-target="#modal_form">添加字段</button>
        <button class="btn btn-info btn-xs margin-r-5 margin-t-5" onclick="location.href='/admin/modules/{{ $module->id }}/generate';">生成模块代码</button>
        <button class="btn btn-info btn-xs margin-r-5 margin-t-5" onclick="location.href='/admin/modules/{{ $module->id }}/migrate';">生成数据结构</button>
    </li>
</ul>
<div id="tabs_table" class="tab-content">
    <div id="tab_field" class="tab-pane fade in active padding-t-15">
        <div class="form-group">
            <div class="col-sm-12">
                <table id="table_field" data-toggle="table" data-url="/admin/modules/fields/{{ $module->id }}/table">
                    <thead>
                    <tr>
                        <th data-field="name" data-width="60">英文名称</th>
                        <th data-field="title" data-width="90">中文名称</th>
                        <th data-field="label" data-width="90">标签名称</th>
                        <th data-field="type_name" data-align="center" data-width="60">类型</th>
                        <th data-field="default" data-align="center" data-width="45">默认值</th>
                        <th data-field="required" data-align="center" data-width="45" data-formatter="booleanFormatter">必填</th>
                        <th data-field="unique" data-align="center" data-width="45" data-formatter="booleanFormatter">唯一</th>
                        <th data-field="min_length" data-align="center" data-width="45">最小长度</th>
                        <th data-field="max_length" data-align="center" data-width="45">最大长度</th>
                        <th data-field="index" data-align="center" data-width="45" data-editable="true">序号</th>
                        <th data-field="action" data-align="center" data-width="60" data-formatter="actionFormatter" data-events="actionEvents">操作</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <div id="tab_column" class="tab-pane fade padding-t-15">
        <div class="form-group">
            <div class="col-sm-12">
                <table id="table_column" data-toggle="table" data-url="/admin/modules/fields/{{ $module->id }}/table">
                    <thead>
                    <tr>
                        <th data-field="name" data-width="60">英文名称</th>
                        <th data-field="title" data-width="90">中文名称</th>
                        <th data-field="label" data-width="90">标签名称</th>
                        <th data-field="column_show" data-align="center" data-width="45" data-formatter="booleanFormatter">显示</th>
                        <th data-field="column_align_name" data-align="center" data-width="45">对齐</th>
                        <th data-field="column_width" data-align="center" data-width="45" data-editable="true">宽度</th>
                        <th data-field="column_editable" data-align="center" data-width="45" data-formatter="booleanFormatter">可编辑</th>
                        <th data-field="column_formatter" data-align="center" data-width="45">格式</th>
                        <th data-field="column_index" data-align="center" data-width="45" data-editable="true">序号</th>
                        <th data-field="action" data-align="center" data-width="60" data-formatter="actionFormatter" data-events="actionEvents">操作</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <div id="tab_editor" class="tab-pane fade padding-t-15">
        <div class="form-group">
            <div class="col-sm-12">
                <table id="table_editor" data-toggle="table" data-url="/admin/modules/fields/{{ $module->id }}/table">
                    <thead>
                    <tr>
                        <th data-field="name" data-width="60">英文名称</th>
                        <th data-field="title" data-width="90">中文名称</th>
                        <th data-field="label" data-width="90">标签名称</th>
                        <th data-field="editor_show" data-align="center" data-width="45" data-formatter="booleanFormatter">显示</th>
                        <th data-field="editor_type_name" data-align="center" data-width="60">编辑器</th>
                        <th data-field="editor_options" data-align="center" data-width="90">选项</th>
                        <th data-field="editor_rows" data-align="center" data-width="45" data-editable="true">行数</th>
                        <th data-field="editor_columns" data-align="center" data-width="45" data-editable="true">列数</th>
                        <th data-field="editor_group" data-align="center" data-width="90">分组</th>
                        <th data-field="editor_index" data-align="center" data-width="45" data-editable="true">序号</th>
                        <th data-field="action" data-align="center" data-width="60" data-formatter="actionFormatter" data-events="actionEvents">操作</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
