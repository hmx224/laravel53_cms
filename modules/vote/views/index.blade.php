@extends('admin.layouts.master')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                投票管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">投票管理</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            @include('admin.layouts.confirm', ['message' => '您确认删除该条信息吗？'])
                            @include('admin.layouts.modal', ['id' => 'modal_comment'])
                            @include('vote.views.toolbar')
                            @include('admin.contents.push')
                            <table id="table" data-toggle="table">
                                <thead>
                                <tr class="parent">
                                    <th data-field="state" data-checkbox="true"></th>
                                    <th data-field="id" data-align="center" data-width="30">ID</th>
                                    <th data-field="title" data-formatter="titleFormatter">标题</th>
                                    <th data-field="amount" data-align="center" data-width="30">参与人数</th>
                                    <th data-field="begin_date" data-align="center" data-width="120">投票开始日期</th>
                                    <th data-field="end_date" data-align="center" data-width="120">投票截止日期</th>
                                    <th data-field="state_name" data-align="center" data-width="60"
                                        data-formatter="stateFormatter">状态
                                    </th>
                                    <th data-field="published_at" data-align="center" data-width="120">发布时间</th>
                                    <th data-field="action" data-align="center" data-width="190"
                                        data-formatter="actionFormatter" data-events="actionEvents">管理操作
                                    </th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection


@section('js')
    <script>
        /* 启动排序 */
        $('#btn_sort').click(function () {
            if ($('#btn_sort').hasClass('active')) {
                $('#btn_sort').removeClass('active');
                $('#btn_sort').text('排序');
                $('#table tbody').sortable('disable');
                $('#table tbody').enableSelection();
                toast('info', '<b>已禁用排序功能</b>')
            }
            else {
                $('#btn_sort').addClass('active');
                $('#btn_sort').text('排序(已启用)');
                $('#table tbody').sortable('enable');
                $('#table tbody').disableSelection();
                toast('info', '<b>已启用排序功能</b>')
            }
        });

        /* 表格 */
        var place_index;
        var select_index;
        var original_y;
        var move_down = 1;
        $('#table').bootstrapTable({
            method: 'get',
            url: '{{$base_url}}/table',
            pagination: true,
            pageNumber: 1,
            pageSize: 20,
            pageList: [10, 25, 50, 100],
            sidePagination: 'server',
            clickToSelect: true,
            striped: true,
            onLoadSuccess: function (data) {
                $('#modal_query').modal('hide');
                $('#table tbody').sortable({
                    cursor: 'move',
                    axis: 'y',
                    revert: true,
                    start: function (e, ui) {
                        select_index = ui.item.attr('data-index');
                        original_y = e.pageY;
                    },
                    sort: function (e, ui) {
                        if (e.pageY > original_y) {
                            place_index = $(this).find('tr').filter('.ui-sortable-placeholder').prev('tr').attr('data-index');
                            move_down = 1;
                        }
                        else {
                            place_index = $(this).find('tr').filter('.ui-sortable-placeholder').next('tr').attr('data-index');
                            move_down = 0;
                        }
                    },
                    update: function (e, ui) {
                        var select_id = data.rows[select_index].id;
                        var place_id = data.rows[place_index].id;

                        if (select_id == place_id) {
                            return;
                        }

                        $.ajax({
                            url: '{{$base_url}}/sort',
                            type: 'get',
                            async: true,
                            data: {select_id: select_id, place_id: place_id, move_down: move_down},
                            success: function (data) {
                                if (data.status_code != 200) {
                                    $('#table tbody').sortable('cancel');
                                    $('#table').bootstrapTable('refresh');
                                }
                            },
                        });
                    }
                });
                $('#table tbody').sortable('disable');
            },
            queryParams: function (params) {
                params.state = $('#state').val();
                params._token = '{{ csrf_token() }}';
                return params;
            },
        });

        function titleFormatter(value, row, index) {
            return '<a href="/vote/detail-' + row.id + '.html" target="_blank">' + row.title + '</a>' +
                    (row.top ? '<span class="badge badge-default pull-right"> 置顶</span>' : '') +
                    (row.tags.indexOf('{{\App\Models\Tag::RECOMMEND}}') >= 0 ? '<span class="badge badge-default pull-right"> 推荐</span>' : '')
        }

        function actionFormatter(value, row, index) {
            //编辑
            var html = '<button class="btn btn-primary btn-xs margin-r-5 edit" data-toggle="tooltip" data-placement="top" title="编辑"><i class="fa fa-edit"></i></button>';

            //置顶
            html += '<button class="btn btn-primary btn-xs margin-r-5 top" data-toggle="tooltip" data-placement="top" title="' + (row.top ? '取消置顶' : '置顶') + '"><i class="fa ' + (row.top ? 'fa-chevron-circle-down' : 'fa-chevron-circle-up') + '"></i></button>';

            //推荐
            html += '<button class="btn btn-primary btn-xs margin-r-5 tag" data-toggle="tooltip" data-placement="top" title="推荐"><i class="fa fa-hand-o-right"></i></button>';

            //统计
            html += '<button class="btn btn-info btn-xs  margin-r-5 count" data-toggle="modal" data-target="#modal_count" title="统计"><i class="fa fa-pie-chart"></i></button>';

            //评论
            html += '<button class="btn btn-info btn-xs margin-r-5 comment" data-toggle="modal" data-target="#modal_comment"><i class="fa fa-comment" data-toggle="tooltip" data-placement="top" title="查看评论"></i></button>';

            //推送
            html += '<button class="btn btn-info btn-xs push" data-toggle="modal" data-target="#modal_push"><i class="fa fa-envelope" data-toggle="tooltip" data-placement="top" title="推送"></i></button>';

            return html;
        }

        window.actionEvents = {
            'click .edit': function (e, value, row, index) {
                window.location.href = '{{$base_url}}/' + row.id + '/edit';
            },

            'click .top': function (e, value, row, index) {
                $.ajax({
                    url: '{{$base_url}}/' + row.id + '/top',
                    type: 'post',
                    data: {'_token': '{{ csrf_token() }}'},
                    success: function (data) {
                        $('#table').bootstrapTable('refresh');
                    },
                    error: function () {
                        toast('error', '操作失败');
                    }
                })
            },

            'click .tag': function (e, value, row, index) {
                $.ajax({
                    url: '{{$base_url}}/' + row.id + '/tag',
                    type: 'post',
                    data: {'_token': '{{ csrf_token() }}', 'tag': '{{ App\Models\Tag::RECOMMEND  }}'},
                    success: function (data) {
                        $('#table').bootstrapTable('refresh');
                    },
                    error: function () {
                        toast('error', '操作失败');
                    }
                })
            },

            'click .count': function (e, value, row, index) {
                $('.common').prop('id', 'modal_count');
                $('#modal_title').text('投票统计');
                $('#window_msg').hide();

                var url = '{{$base_url}}/items/' + row.id;
                $.ajax({
                    url: url,
                    type: "get",
                    data: {'_token': '{{ csrf_token() }}'},
                    dataType: 'html',
                    success: function (html) {
                        $('#contents').html(html);
                    }
                });
            },

            'click .comment': function (e, value, row, index) {
                $('.common').prop('id', 'modal_comment');
                $('#modal_title').text('查看评论');
                $('#window_msg').hide();

                var url = '{{$base_url}}/comments/' + row.id;
                $.ajax({
                    url: url,
                    type: "get",
                    data: {'_token': '{{ csrf_token() }}'},
                    dataType: 'html',
                    success: function (html) {
                        $('#contents').html(html);
                    }
                });
            },

            'click .push': function (e, value, row, index) {
                $('#push_id').val(row.id);
                $('#push_title').val(row.title);
            }
        }

        function stateFormatter(value, row, index) {
            var style = 'label-primary';
            switch (row.state_name) {
                case '未发布':
                    style = 'label-primary';
                    break;
                case '已发布':
                    style = 'label-success';
                    break;
                case '已撤回':
                    style = 'label-warning';
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
@endsection