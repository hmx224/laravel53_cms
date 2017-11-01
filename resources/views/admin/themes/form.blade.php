<div class="modal fade common" id="modal_theme" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:640px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                <h4 class="modal-title">请输入主题信息</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-info">
                            <form id="form_theme" action="/admin/themes" method="post" class="form-horizontal">
                                {{ csrf_field() }}
                                <input id="method" name="_method" type="hidden" value="POST">
                                <div class="box-body">
                                    <div class="form-group">
                                        {!! Form::label('name', '英文名称:', ['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-4">
                                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        </div>
                                        {!! Form::label('title', '中文名称', ['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-4">
                                            {!! Form::text('title', null, ['class' => 'form-control']) !!}
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
<div class="modal fade common" id="modal_file" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:640px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                <h4 class="modal-title">请输入文件信息</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-info">
                            <form id="form_file" action="/admin/themes/file" method="post" class="form-horizontal">
                                {{ csrf_field() }}
                                <div class="box-body">
                                    <div class="form-group">
                                        {!! Form::label('name', '文件名:', ['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-10">
                                            <div class="input-group">
                                                <span class="input-group-addon" id="path"></span>
                                                {!! Form::hidden('path') !!}
                                                {!! Form::text('name', null, ['class' => 'form-control', 'pattern' => '^[a-zA-Z]\w{1,20}$']) !!}
                                                {!! Form::hidden('extension') !!}
                                                <span class="input-group-addon" id="extension"></span>
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
