<div class="modal fade common" id="modal_query" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:640px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                <h4 class="modal-title">请输入查询条件</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-info">
                            <form id="form_query" class="form-horizontal">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">ID:</label>
                                        <div class="col-sm-4">
                                            <input id="id" name="id" class="form-control" placeholder="">
                                        </div>
                                        <label class="col-sm-2 control-label">会员类型:</label>
                                        <div class="col-sm-4">
                                            {!! Form::select('type', array_prepend(\App\Models\Member::TYPES, '', 0), null, ['class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">手机号:</label>
                                        <div class="col-sm-4">
                                            <input id="mobile" name="mobile" class="form-control" placeholder="">
                                        </div>
                                        <label class="col-sm-2 control-label">会员昵称:</label>
                                        <div class="col-sm-4">
                                            <input id="nick_name" name="nick_name" class="form-control" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="start_date" class="control-label col-sm-2">开始日期:</label>
                                        <div class="col-sm-4">
                                            <div class="input-group date" id="start_date">
                                                <input class="form-control" name="start_date" type="text" id="start_date">
                                                <span class="input-group-addon"> <span class="glyphicon glyphicon-calendar"></span> </span>
                                            </div>
                                        </div>
                                        <label for="end_date" class="control-label col-sm-2">截止日期:</label>
                                        <div class="col-sm-4">
                                            <div class="input-group date" id="end_date">
                                                <input class="form-control" name="end_date" type="text" id="end_date">
                                                <span class="input-group-addon"> <span class="glyphicon glyphicon-calendar"></span> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button class="btn btn-default" data-dismiss="modal">取消</button>
                                    <button class="btn btn-info pull-right" id="btn_query" data-dismiss="modal">查询
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('.date').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        locale: "zh-CN",
        toolbarPlacement: 'bottom',
        showClear: true,
    });

    $('#btn_query').click(function () {
        $('#table').bootstrapTable('selectPage', 1);
        $('#table').bootstrapTable('refresh');
    });
</script>