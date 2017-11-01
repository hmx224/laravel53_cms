<div class="modal fade common" id="modal_form" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:640px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                <h4 class="modal-title">请输入字段信息</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-info">
                            <form id="form" action="/admin/modules" method="post" class="form-horizontal">
                                {{ csrf_field() }}
                                <input id="method" name="_method" type="hidden" value="POST">
                                <input type="hidden" name="module_id" value="{{ $module->id }}"/>
                                <div class="box-body">
                                    <ul id="tabs" class="nav nav-tabs">
                                        <li class="active">
                                            <a href="#tab_form_field" data-toggle="tab"><i class="fa fa-bars"></i> 字段</a>
                                        </li>
                                        <li>
                                            <a href="#tab_form_column" data-toggle="tab"><i class="fa fa-table"></i> 表格</a>
                                        </li>
                                        <li>
                                            <a href="#tab_form_editor" data-toggle="tab"><i class="fa fa-pencil"></i> 表单</a>
                                        </li>
                                    </ul>
                                    <div id="tabs_form" class="tab-content">
                                        <div id="tab_form_field" class="tab-pane fade in active padding-t-15">
                                            <div class="form-group">
                                                {!! Form::label('name', '英文名称:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                                </div>
                                                {!! Form::label('title', '中文名称:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('title', null, ['class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('label', '标签名称:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('label', null, ['class' => 'form-control']) !!}
                                                </div>
                                                {!! Form::label('index', '序号:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('index', null, ['class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('type', '类型', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::select('type', \App\Models\ModuleField::TYPES, null, ['class' => 'form-control']) !!}
                                                </div>
                                                {!! Form::label('default', '默认值:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('default', null, ['class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('required', '必填:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::checkbox('required', 1, null, ['class' => 'switch']) !!}
                                                </div>
                                                {!! Form::label('unique', '唯一:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::checkbox('unique', 1, null, ['class' => 'switch']) !!}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('min_length', '最小长度:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('min_length', 0, ['class' => 'form-control']) !!}
                                                </div>
                                                {!! Form::label('max_length', '最大长度:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('max_length', 0, ['class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div id="tab_form_column" class="tab-pane fade padding-t-15">
                                            <div class="form-group">
                                                {!! Form::label('column_show', '是否显示:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::checkbox('column_show', 1, null, ['class' => 'switch']) !!}
                                                </div>
                                                {!! Form::label('column_editable', '是否可编辑:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::checkbox('column_editable', 1, null, ['class' => 'switch']) !!}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('column_align', '对齐方式:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::select('column_align', \App\Models\ModuleField::COLUMN_ALIGNS, null, ['class' => 'form-control']) !!}
                                                </div>
                                                {!! Form::label('column_width', '宽度:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('column_width', null, ['class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('column_formatter', '格式:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('column_formatter', null, ['class' => 'form-control']) !!}
                                                </div>
                                                {!! Form::label('column_index', '序号:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('column_index', null, ['class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div id="tab_form_editor" class="tab-pane fade padding-t-15">
                                            <div class="form-group">
                                                {!! Form::label('editor_show', '是否显示:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::checkbox('editor_show', 1, null, ['class' => 'switch']) !!}
                                                </div>
                                                {!! Form::label('editor_readonly', '是否只读:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::checkbox('editor_readonly', 1, null, ['class' => 'switch']) !!}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('editor_type', '编辑器类型', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::select('editor_type', \App\Models\ModuleField::EDITOR_TYPES, null, ['class' => 'form-control']) !!}
                                                </div>
                                                {!! Form::label('editor_options', '编辑器选项:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('editor_options', null, ['class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('editor_rows', '行数:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('editor_rows', null, ['class' => 'form-control']) !!}
                                                </div>
                                                {!! Form::label('editor_columns', '列数:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('editor_columns', null, ['class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('editor_group', '分组:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::select('editor_group', $groups, null, ['class' => 'form-control']) !!}
                                                </div>
                                                {!! Form::label('editor_index', '序号:', ['class' => 'control-label col-sm-2']) !!}
                                                <div class="col-sm-4">
                                                    {!! Form::text('editor_index', null, ['class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button class="btn btn-default" data-dismiss="modal">取消</button>
                                    <button type="submit" class="btn btn-info pull-right">提交</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
