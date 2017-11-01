<div class="modal fade common" id="modal_form" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:640px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                <h4 class="modal-title">请输入字典信息</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-info">
                            <form id="form" action="/admin/dictionaries" method="post" class="form-horizontal">
                                {{ csrf_field() }}
                                <input id="method" name="_method" type="hidden" value="POST">
                                <input type="hidden" name="parent_id" value="{{ $parent_id }}" id="parent_id">
                                <div class="box-body">
                                    <div class="form-group">
                                        {!! Form::label('code', '编码:', ['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-10">
                                            {!! Form::text('code', null, ['class' => 'form-control']) !!}
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        {!! Form::label('name', '名称:',['class' => 'control-label col-sm-2']) !!}
                                        <div class="col-sm-10">
                                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        {!! Form::label('value', '值:',['class'=>'control-label col-sm-2']) !!}
                                        <div class="col-sm-10">
                                            {!! Form::text('value', null, ['class' => 'form-control']) !!}
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
