<div class="modal fade" id="modal_push" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                <h4 class="modal-title">消息推送</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-info">
                            <form id="form_push" action="/admin/contents/push" method="post" class="form-horizontal">
                                {!! Form::hidden('content_id', null, ['id' => 'content_id', 'class' => 'form-control']) !!}
                                <div class="box-body">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">iOS:</label>
                                        <div class="col-sm-4">
                                            {!! Form::select('ios', \App\Models\PushLog::IOS_PUSH_OPTIONS, null, ['class' => 'form-control']) !!}
                                        </div>
                                        <label class="col-sm-2 control-label">Android:</label>
                                        <div class="col-sm-4">
                                            {!! Form::select('android', \App\Models\PushLog::ANDROID_PUSH_OPTIONS, null, ['class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">标签:</label>
                                        <div class="col-sm-4">
                                            {!! Form::text('tag', null, ['class' => 'form-control']) !!}
                                        </div>
                                        <label class="col-sm-2 control-label">别名:</label>
                                        <div class="col-sm-4">
                                            {!! Form::text('alias', null, ['class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button class="btn btn-default" data-dismiss="modal">取消</button>
                                    <button class="btn btn-info pull-right" id="btn_send" data-dismiss="modal">发送</button>
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
    $('#btn_send').click(function () {
        var params = $('#form_push input,#form_push select').serializeArray();
        var str = $.param(params);

        $.ajax({
            url: '/admin/contents/push?' + str,
            type: 'post',
            data: {'_token': '{{ csrf_token() }}'},
            success: function (data) {
                if (data.status_code != 200) {
                    return window.location.reload();
                }
                toast('success', '发送成功！<a href="/push/logs">查看推送日志</a>');
            },
            error: function () {
                //发送失败提示
                toast('error', '发送失败');
            },
        })
    });
</script>
