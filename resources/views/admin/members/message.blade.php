<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <div class="box-body">
                <table id="table" data-toggle="table" style="word-break:break-all;">
                    <thead>
                    <tr>
                        <th data-field="id" data-width="50" data-align="center">ID</th>
                        <th data-field="type_name" data-width="60" data-align="center">类型</th>
                        <th data-field="title" data-width="300" >标题</th>
                        <th data-field="member_id" data-width="60" data-align="center">会员ID</th>
                        <th data-field="state_name" data-width="60" data-align="center" data-formatter="stateFormatter">状态</th>
                        <th data-field="created_at" data-width="120" data-align="center">发表时间</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $('#table').bootstrapTable({
        method: 'get',
        url: '/admin/messages/table',
        pagination: true,
        pageNumber: 1,
        pageSize: 15,
        pageList: [20, 50, 100, 500],
        sidePagination: 'server',
        clickToSelect: true,
        striped: true,
        queryParams: function (params) {
            params.id = '{{ $member_id }}';
            params._token = '{{ csrf_token() }}';
            return params;
        },
    });

    function stateFormatter(value, row, index) {
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
</script>