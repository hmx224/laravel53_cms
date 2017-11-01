@extends('admin.layouts.master')
@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                订单明细
            </h1>
            <ol class="breadcrumb">
                <li><a href="/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">订单明细</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            @include('admin.layouts.confirm', ['message' => '您确认删除该条信息吗？'])
                            @include('admin.orders.toolbar')
                            @include('admin.orders.query')
                            <table id="table"
                                   data-toggle="table"
                                   data-show-export="true"
                                   data-toolbar="#toolbar">
                                <thead>
                                <tr>
                                    <th data-field="id" data-width="55" data-align="center">ID</th>
                                    <th data-field="code" data-width="125" data-align="center">订单编号</th>
                                    <th data-field="type_name" data-width="65" data-align="center">订单类型</th>
                                    <th data-field="name" data-width="200">名称</th>
                                    <th data-field="num" data-width="60" data-align="center">数量</th>
                                    <th data-field="fee" data-width="60" data-align="center">金额</th>
                                    <th data-field="pay_name" data-width="75" data-align="center">支付类型</th>
                                    <th data-field="nick_name" data-align="center" data-width="100">会员</th>
                                    <th data-field="mobile" data-align="center" data-width="100">手机号</th>
                                    <th data-field="state_name" data-width="60"
                                        data-formatter="stateFormatter"
                                        data-align="center">状态
                                    </th>
                                    <th data-field="created_at" data-width="105" data-align="center">创建时间
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
        $('#table').bootstrapTable({
            method: 'get',
            url: '/admin/orders/table',
            pagination: true,
            pageNumber: 1,
            pageSize: 20,
            pageList: [20, 50, 100, 500],
            sidePagination: 'server',
            clickToSelect: true,
            striped: true,
            queryParams: function (params) {
                var object = $('#form_query input,#form_query select').serializeObject();
                object['state'] = $('#state').val();
                object['offset'] = params.offset;
                object['limit'] = params.limit;
                return object;
            },
        });

        function actionFormatter(value, row, index) {
            var disabled_del = '';
            switch (row.state_name) {
                case '已取消':
                    disabled_del = 'disabled="disabled"';
                    break;
            }
            return [
                '<a class="remove" href="javascript:void(0)"><button class="btn btn-danger btn-xs" ' + disabled_del + ' data-toggle="modal" data-target="#modal">取消</button></a>',
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
                url: '/admin/orders/' + row_id + '/delete',
                success: function (data) {
                    window.location.href = '/admin/orders';
                }
            });
        });

        window.actionEvents = {
            'click .edit': function (e, value, row, index) {
                window.location.href = '/admin/orders/' + row.id + '/edit';
            },
            'click .details': function (e, value, row, index) {
                window.location.href = '/admin/orders/' + row.id;
            },
            'click .remove': function (e, value, row, index) {
                remove_open = true;
                $('#msg').html('您确认取消这件订单吗？');
                $('#modal_remove').show();
                $('#modal_remove').data('id', row.id);
            },

        };

        function titleFormatter(value, row, index) {
            return [
                '<a href="/admin/orders/' + row.id + '" target="_blank">' + row.title + '</a>',
            ]
        }

        function stateFormatter(value, row, index) {
            var style = 'label-primary';
            switch (row.state_name) {
                case '已取消':
                    style = 'label-danger';
                    break;
                case '待支付':
                    style = 'label-primary';
                    break;
                case '已完成':
                    style = 'label-success';
                    break;
                case '待退款':
                    style = 'label-warning';
                    break;
                case '已退款':
                    style = 'label-success';
                    break;
            }
            return [
                '<span class="label ' + style + '">' + row.state_name + '</span>',
            ].join('');
        }
    </script>

@endsection