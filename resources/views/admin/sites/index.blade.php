@extends('admin.layouts.master')
@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                站点管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">站点管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-info">
                        @include('admin.layouts.flash')
                        @include('admin.layouts.confirm', ['message' => '您确认删除该条信息吗？'])
                        <div class="box-header">
                            <button class="btn btn-primary btn-xs margin-r-5 margin-t-5" onclick="window.location.href='/admin/sites/create';"> 添加站点</button>
                        </div>
                        <div class="box-body">
                            <table data-toggle="table"
                                   data-url="sites/table">
                                <thead>
                                <tr>
                                    <th data-field="id" data-align="center" data-width="45">ID</th>
                                    <th data-field="name" data-width="90">英文名称</th>
                                    <th data-field="title">标题</th>
                                    <th data-field="domain" data-width="200">域名</th>
                                    <th data-field="directory" data-width="200">目录</th>
                                    <th data-field="user_name" data-align="center" data-width="90">操作员</th>
                                    <th data-field="updated_at" data-align="center" data-width="120">更新时间</th>
                                    <th data-field="action" data-formatter="actionFormatter" data-events="actionEvents" data-align="center" data-width="100">操作</th>
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
                '<a class="edit" href="javascript:void(0)"><button class="btn btn-primary btn-xs margin-r-5">编辑</button></a>',
                '<a class="publish" href="javascript:void(0)"><button class="btn btn-primary btn-xs margin-r-5">发布</button></a>',
            ].join('');
        }

        $("#modal_remove").click(function () {
            var row_id = $(this).data('id');

            $.ajax({
                type: 'get',
                data: {'_token': '{{ csrf_token() }}'},
                url: '/admin/sites/' + row_id + '/delete',
                success: function (data) {
                    window.location.href = '/admin/sites';
                }
            });
        });

        window.actionEvents = {
            'click .edit': function (e, value, row, index) {
                window.location.href = '/admin/sites/' + row.id + '/edit';
            },
            'click .publish': function (e, value, row, index) {
                window.location.href = '/admin/sites/' + row.id + '/publish';
            },
        };

    </script>

@endsection