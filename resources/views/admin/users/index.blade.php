@extends('admin.layouts.master')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                用户管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li><a href="#">系统管理</a></li>
                <li class="active">用户管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            @include('admin.layouts.confirm', ['message' => '您确认注销该条信息吗？'])
                            @include('admin.users.tree', ['id' => 'modal_category'])
                            <div id="toolbar" class="btn-group margin-b-5">
                                <button class="btn btn-primary btn-xs margin-r-5 margin-b-5" id="create"
                                        onclick="javascript:window.location.href='/admin/users/create'">新增用户
                                </button>
                            </div>

                            <table data-toggle="table"
                                   data-url="users/table"
                                   data-pagination="true">
                                <thead>
                                <tr>
                                    <th data-field="id" data-align="center">ID</th>
                                    <th data-field="username">用户名</th>
                                    <th data-field="name">姓名</th>
                                    <th data-field="role_name">角色</th>
                                    <th data-field="site_name">站点</th>
                                    <th data-field="state_name" data-width="60" data-align="center"
                                        data-formatter="stateFormatter">状态
                                    </th>
                                    <th data-field="action" data-width="150" data-formatter="actionFormatter"
                                        data-events="actionEvents" data-align="center">操作
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
    <script>
        function actionFormatter(value, row, index) {
            return [
                '<a class="edit" href="javascript:void(0)"><button class="btn btn-primary btn-xs">编辑</button></a>',
                '<span> </span>',
                '<a class="category" href="javascript:void(0)"><button class="btn btn-success btn-xs" data-toggle="modal" data-target="#modal_category">栏目</button></a>',
                '<span> </span>',
                '<a class="remove" href="javascript:void(0)"><button class="btn btn-danger btn-xs" ' + (row.state_name == '已注销' ? 'disabled="disabled"' : '') + ' data-toggle="modal" data-target="#modal">注销</button></a>',
            ].join('');
        }

        $("#modal_remove").click(function () {
            var row_id = $(this).data('id');

            $.ajax({
                type: 'get',
                data: {'_token': '{{ csrf_token() }}'},
                url: '/admin/users/' + row_id + '/delete',
                success: function (data) {
                    window.location.href = '/admin/users';
                }
            });
        });

        window.actionEvents = {
            'click .edit': function (e, value, row, index) {
                window.location.href = '/admin/users/' + row.id + '/edit';
            },
            'click .remove': function (e, value, row, index) {
                $('#modal_remove').data('id', row.id);
            },
            'click .category': function (e, value, row, index) {
                $('#btn_tree_submit').data('id', row.id);

                $.ajax({
                    type: 'POST',
                    data: {'_token': '{{ csrf_token() }}'},
                    url: '/admin/users/category/' + row.id,
                    success: function (category_ids) {
                        getTrees(row.id, category_ids);
                    }
                });
            },
        };

        function stateFormatter(value, row, index) {
            var style = 'label-primary';
            switch (row.state_name) {
                case '正常':
                    style = 'label-success';
                    break;
                case '已注销':
                    style = 'label-danger';
                    break;
            }
            return [
                '<span class="label ' + style + '">' + row.state_name + '</span>',
            ].join('');
        }
    </script>
@endsection