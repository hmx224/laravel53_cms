<div class="modal fade common1" id="modal_query" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:640px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                <h4 class="modal-title">请输入搜索条件</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-info">
                            <form id="form_query" class="form-horizontal">
                                <div class="box-body">
                                    {{--<input type="hidden" id="category_id" name="category_id" value="">--}}
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">ID:</label>
                                        <div class="col-sm-4">
                                            {!! Form::text('id', null, ['class' => 'form-control']) !!}
                                        </div>
                                        <label class="col-sm-2 control-label">操作员:</label>
                                        <div class="col-sm-4">
                                            {!! Form::text('user_name', null, ['class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">标题:</label>
                                        <div class="col-sm-10">
                                            {!! Form::text('title', null, ['class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="start_date" class="control-label col-sm-2">起始日期:</label>
                                        <div class="col-sm-4">
                                            <div class="input-group date" id="start_date">
                                                <input class="form-control" name="start_date" type="text" id="start_date">
                                                <span class="input-group-addon"> <span class="fa fa-calendar"></span> </span>
                                            </div>
                                        </div>
                                        <label for="end_date" class="control-label col-sm-2">截止日期:</label>
                                        <div class="col-sm-4">
                                            <div class="input-group date" id="end_date">
                                                <input class="form-control" name="end_date" type="text" id="end_date">
                                                <span class="input-group-addon"> <span class="fa fa-calendar"></span> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button class="btn btn-default" data-dismiss="modal">取消</button>
                                    <button class="btn btn-info pull-right" id="btn_query" data-dismiss="modal">查询
                                    </button>
                                    <button type="reset" class="btn btn-default pull-right margin-r-5">清除
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
    });
</script>