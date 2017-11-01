@extends('admin.layouts.master')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                消息管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">消息管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            @include('admin.layouts.confirm', ['message' => '您确认删除该条信息吗？'])
                            @include('admin.messages.toolbar')
                            <table id="table" data-toggle="table" style="word-break:break-all;">
                                <thead>
                                <tr>
                                    <th data-field="state" data-checkbox="true"></th>
                                    <th data-field="id" data-width="50" data-align="center">ID</th>
                                    <th data-field="type_name" data-width="60" data-align="center">类型</th>
                                    <th data-field="title" data-width="200" >标题</th>
                                    <th data-field="content">内容</th>
                                    <th data-field="member_id" data-width="60" data-align="center">会员ID</th>
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
            url: '/admin/messages/table',
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

            $.ajax({
                type: 'get',
                data: {'_token': '{{ csrf_token() }}'},
                url: '/admin/messages/' + row_id + '/delete',
                success: function (data) {
                    window.location.href = '/admin/messages';
                }
            });
        });

        // $('#modal').modal('hide');
        $('#modal').on('hide.bs.modal', function (event) {

        });

        window.actionEvents = {
            'click .pass': function (e, value, row, index) {
                $.ajax({
                    type: 'get',
                    data: {'_token': '{{ csrf_token() }}'},
                    url: '/admin/messages/pass/' + row.id,
                    success: function (data) {
                        window.location.href = '/admin/messages';
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