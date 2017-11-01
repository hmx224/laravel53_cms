<div class="modal fade common" id="modal_form" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:640px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                <h4 class="modal-title">请输入模块信息</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-info">
                            <form id="form" action="/admin/modules" method="post" class="form-horizontal">
                                {{ csrf_field() }}
                                <input id="method" name="_method" type="hidden" value="POST">
                                <input id="module_id" name="module_id" type="hidden" value="">
                                <div class="box-body">
                                    <div class="form-group">
                                        {!! Form::label('name', '英文名称:', ['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-4">
                                            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => '英文首字母大写']) !!}
                                        </div>
                                        {!! Form::label('title', '中文名称', ['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-4">
                                            {!! Form::text('title', null, ['class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('table_name', '数据表名', ['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-4">
                                            {!! Form::text('table_name', null, ['class' => 'form-control', 'placeholder' => '英文首字母小写、复数']) !!}
                                        </div>
                                        {!! Form::label('use_category', '是否用栏目:', ['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-4">
                                            {!! Form::checkbox('use_category', 1, null, ['class' => 'switch']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('icon', '图标', ['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-4">
                                            <div class="input-group">
                                                {!! Form::text('icon', 'fa-cube', ['class' => 'form-control']) !!}
                                                <span class="input-group-addon"></span>
                                            </div>
                                        </div>
                                        {!! Form::label('is_lock', '是否锁定:', ['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-4">
                                            {!! Form::checkbox('is_lock', 1, null, ['class' => 'switch']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('groups', '编辑器分组', ['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-10">
                                            {!! Form::text('groups', null, ['class' => 'form-control', 'placeholder' => '基本信息,正文']) !!}
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