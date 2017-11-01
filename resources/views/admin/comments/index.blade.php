@extends('admin.layouts.master')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                评论管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">评论管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            @include('admin.layouts.confirm', ['message' => '您确认删除该条信息吗？'])
                            @include('admin.comments.toolbar')
                            <table id="table" data-toggle="table">
                                <thead>
                                <tr>
                                    <th data-field="state" data-checkbox="true"></th>
                                    <th data-field="id" data-width="50" data-align="center">ID</th>
                                    <th data-field="refer_id" data-width="50">原文ID</th>
                                    <th data-field="content" data-width="220" data-formatter="contentFormatter">评论</th>
                                    <th data-field="nick_name" data-width="120" data-align="center">会员</th>
                                    <th data-field="ip" data-width="100" data-align="center">IP</th>
                                    <th data-field="state_name" data-width="60" data-align="center" data-formatter="stateFormatter">状态</th>
                                    <th data-field="created_at" data-width="120" data-align="center">发表时间</th>
                                    <th data-field="action" data-width="100" data-align="center" data-formatter="actionFormatter" data-events="actionEvents"> 操作</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        $('#table').bootstrapTable({
            method: 'get',
            url: '/admin/comments/table',
            pagination: true,
            pageNumber: 1,
            pageSize: 20,
            pageList: [10, 25, 50, 100],
            sidePagination: 'server',
            clickToSelect: true,
            striped: true,
            queryParams: function (params) {
                params.state = $('#state').val();
                params._token = '{{ csrf_token() }}';
                return params;
            },
        });

        function contentFormatter(value, row, index) {
            return '<a href="/admin/contents/' + row.refer_id + '" target="_blank"  class="content_title" data-toggle="tooltip" data-placement="top" title="' + value + '">' + value + '</a>';
        }

        function actionFormatter(value, row, index) {
            var disabled_passed = '';
            var disabled_del = '';
            switch (row.state_name) {
                case '已审核':
                    disabled_passed = 'disabled="disabled"';
                    break;
                case '已删除':
                    disabled_del = 'disabled="disabled"';
                    break;
            }
            return [
                '<a class="pass" href="javascript:void(0)"><button class="btn btn-success btn-xs" ' + disabled_passed + '>审核</button></a>',
                '<span> </span>',
                '<a class="remove" href="javascript:void(0)"><button class="btn btn-danger btn-xs" ' + disabled_del + ' data-toggle="modal" data-target="#modal">删除</button></a>',
            ].join('');
        }

        $("#modal_remove").click(function () {
            var row_id = $(this).data('id');

            if (typeof(row_id) == "undefined") {
                return false;
            }

            var ids = [row_id];
            $.ajax({
                url: '/admin/comments/state',
                type: 'POST',
                data: {'_token': '{{ csrf_token() }}', 'ids': ids, 'state': '{{ \App\Models\Comment::STATE_DELETED }}'},
                success: function () {
                    $('#modal').modal('hide');
                    $('#table').bootstrapTable('refresh');
                }
            });
        });

        // $('#modal').modal('hide');
        $('#modal').on('hide.bs.modal', function (event) {

        });

        window.actionEvents = {
            'click .pass': function (e, value, row, index) {
                var ids = [row.id];
                $.ajax({
                    url: '/admin/comments/state',
                    type: 'POST',
                    data: {'_token': '{{ csrf_token() }}', 'ids': ids, 'state': '{{ \App\Models\Comment::STATE_PASSED }}'},
                    success: function () {
                        $('#table').bootstrapTable('refresh');
                    }
                });
            },
            'click .remove': function (e, value, row, index) {
                remove_open = true;
                $('#msg').html('您确认删除该条信息吗？');
                $('#modal_remove').show();
                $('#modal_remove').data('id', row.id);
            },

        };

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

@endsection