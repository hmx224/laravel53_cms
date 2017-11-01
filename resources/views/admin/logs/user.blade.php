@extends('admin.layouts.master')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                操作日志
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">操作日志</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            <div class="cb-toolbar"></div>
                            <div class="btn-group margin-bottom pull-right">
                                <button type="button" class="btn btn-default btn-xs margin-r-5" id="query" data-toggle="modal" data-target="#modal_query">查询</button>
                            </div>
                            <table id="table" data-toggle="table">
                                <thead>
                                <tr>
                                    <th data-field="id" data-width="90" data-align="center">ID</th>
                                    <th data-field="site_title" data-width="120" data-align="center">站点</th>
                                    <th data-field="action">操作</th>
                                    <th data-field="refer_id" data-width="90" data-align="center">关联ID</th>
                                    <th data-field="refer_type" data-width="150" data-align="center">关联类型</th>
                                    <th data-field="ip" data-width="90" data-align="center">IP地址</th>
                                    <th data-field="user_name" data-width="90" data-align="center">操作员</th>
                                    <th data-field="created_at" data-width="150" data-align="center">操作时间</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade common" id="modal_query" tabindex="-1" role="dialog">
        <div class="modal-dialog" style="width:640px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                    <h4 class="modal-title">请输入查询条件</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box box-info">
                                <form id="form_query" class="form-horizontal">
                                    <div class="box-body">

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">操作员:</label>
                                            <div class="col-sm-4">
                                                {!! Form::select('user_id', $users, 0, ['class' => 'form-control']) !!}
                                            </div>
                                            <label class="col-sm-2 control-label">操作:</label>
                                            <div class="col-sm-4">
                                                <input id="action" name="action" class="form-control" placeholder="">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="start_date" class="control-label col-sm-2">开始日期:</label>
                                            <div class="col-sm-4">
                                                <div class="input-group date" id="start_date">
                                                    <input class="form-control" name="start_date" type="text" id="start_date">
                                                    <span class="input-group-addon"> <span class="glyphicon glyphicon-calendar"></span> </span>
                                                </div>
                                            </div>
                                            <label for="end_date" class="control-label col-sm-2">截止日期:</label>
                                            <div class="col-sm-4">
                                                <div class="input-group date" id="end_date">
                                                    <input class="form-control" name="end_date" type="text" id="end_date">
                                                    <span class="input-group-addon"> <span class="glyphicon glyphicon-calendar"></span> </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer">
                                        <button class="btn btn-default" data-dismiss="modal">取消</button>
                                        <button class="btn btn-info pull-right" id="btn_query" data-dismiss="modal">查询
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
        $('#table').bootstrapTable({
            method: 'get',
            url: '/admin/users/logs/table',
            pagination: true,
            pageNumber: 1,
            pageSize: 20,
            pageList: [10, 25, 50, 100],
            sidePagination: 'server',
            clickToSelect: true,
            striped: true,
            queryParams: function (params) {
                var object = $('#form_query input,#form_query select').serializeObject();
                object['state'] = $('#state').val();
                object['_token'] = '{{ csrf_token() }}';
                object['offset'] = params.offset;
                object['limit'] = params.limit;
                return object;
            },
        });

        function stateFormatter(value, row, index) {
            var style = 'label-primary';
            switch (row.state_name) {
                case '成功':
                    style = 'label-success';
                    break;
                case '失败':
                    style = 'label-danger';
                    break;
            }
            return [
                '<span class="label ' + style + '">' + value + '</span>',
            ].join('');
        }

        $('.date').datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            locale: "zh-CN",
            toolbarPlacement: 'bottom',
            showClear: true,
        });

        $('#btn_query').click(function () {
            $('#table').bootstrapTable('selectPage', 1);
            $('#table').bootstrapTable('refresh');
        });
    </script>
@endsection