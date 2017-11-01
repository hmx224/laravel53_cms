<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            @include('admin.comments.reply')
            <div class="box-body">
                <table id="comment_table" data-toggle="table" style="word-break:break-all;">
                    <thead>
                    <tr>
                        <th data-field="id" data-width="60">ID</th>
                        <th data-field="nick_name" data-width="100">会员</th>
                        <th data-field="content" data-width="380" data-formatter="commentTitleFormatter">评论</th>
                        <th data-field="ip" data-width="125">IP</th>
                        <th data-field="state_name" data-width="60" data-formatter="commentStateFormatter">状态</th>
                        <th data-field="created_at" data-width="130">发表时间</th>
                        <th data-field="action" data-width="100" data-align="center" data-formatter="commentActionFormatter"
                            data-events="commentActionEvents"> 操作
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <h4 id="admin_replies_title">管理员评论</h4>
        <div class="box box-info">
            <div class="box-body">
                <div class="col-sm-12" style="padding: 0px 0px 10px 0px;">
                    {!! Form::textarea('content', null, ['id'=>'content','class' => 'form-control', 'rows' => '4']) !!}
                </div>
                <button type="submit" class="btn btn-lg btn-info btn-block center-block submit" onclick="confirm()">
                    提交
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $('#comment_table').bootstrapTable({
        method: 'get',
        url: '/admin/comments/table',
        pagination: true,
        pageNumber: 1,
        pageSize: 8,
        pageList: [10, 25, 50, 100],
        sidePagination: 'server',
        clickToSelect: true,
        striped: true,
        queryParams: function (params) {
            params.refer_id = '{{ $refer_id }}';
            params.refer_type = "{{ urlencode($refer_type) }}";
            params._token = '{{ csrf_token() }}';
            return params;
        },
    });

    function commentStateFormatter(value, row, index) {
        var style = 'label-primary';
        switch (row.state_name) {
            case '未审核':
                style = 'label-primary';
                break;
            case '已审核':
                style = 'label-success';
                break;
            case '已删除':
                style = 'label-danger';
                break;
        }
        return [
            '<span class="label ' + style + '">' + row.state_name + '</span>',
        ].join('');
    }

    function commentActionFormatter(value, row, index) {
        var disabled_del = '';
        switch (row.state_name) {
            case '已删除':
                disabled_del = 'disabled="disabled"';
                break;
        }
        return [
            '<a class="remove" href="javascript:void(0)"><button class="btn btn-danger btn-xs" ' + disabled_del + ' >删除</button></a>'
        ].join('');
    }

    window.commentActionEvents = {
        'click .remove': function (e, value, row, index) {
            var ids = [row.id];
            $.ajax({
                url: '/admin/comments/state',
                type: 'POST',
                data: {'_token': '{{ csrf_token() }}', 'ids': ids, 'state': '{{ \App\Models\Comment::STATE_DELETED }}'},
                success: function () {
                    $('#comment_table').bootstrapTable('selectPage', 1);
                    $('#comment_table').bootstrapTable('refresh');

                    toast('success', '删除成功！');
                }
            });
        },
        'click .comment': function (e, value, row, index) {
            $('#replies_title').text('回复列表');

            var url = '/admin/comments/replies/' + row.id;
            $.ajax({
                url: url,
                type: "get",
                data: {
                    '_token': '{{ csrf_token() }}',
                    'refer_id': '{{ $refer_id }}',
                    'refer_type': '{{ urlencode($refer_type) }}'
                },
                dataType: 'html',
                success: function (html) {
                    $('#reply_contents').html(html);
                }
            });
        }
    };

    function confirm() {
        toastr.options = {
            'closeButton': true,
            'showDuration': 100,
            'hideDuration': 0,
            'timeOut': 0,
            'extendedTimeOut': 0,
            'positionClass': 'toast-top-center',
        };
        toastr['info']('您确定提交吗？&nbsp;&nbsp;&nbsp;<span onclick="commit();" style="text-decoration: underline;">确定</span>');
    }

    function commit() {
        var content_val = $.trim($('#content').val());
        if (content_val == '') {
            toast('warning', '请输入评论内容，再提交！');
            return false;
        }

        $.ajax({
            url: '{{ "/admin/comments/$refer_id/reply" }}',
            type: 'post',
            data: {
                'refer_type': '{{ urlencode($refer_type) }}',
                'content': $('#content').val(),
                '_token': '{{ csrf_token() }}'
            },
            success: function (data) {
                if (data.status_code == 200) {
                    $('#comment_table').bootstrapTable('selectPage', 1);
                    $('#comment_table').bootstrapTable('refresh', {silent: true});
                    $('#content').val('');
                    toast('success', '评论成功！');

                } else {
                    toast('error', data.message);
                }
            },
            error: function () {
                toast('warning', '系统繁忙！');
            }
        });
    }

    function commentTitleFormatter(value, row, index) {
        return [
            '<p class="content_title" data-toggle="tooltip" data-placement="top" title="' + row.content + '">' + row.content + '</p>',
        ]
    }
</script>