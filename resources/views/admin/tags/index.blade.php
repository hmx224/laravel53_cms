@extends('admin.layouts.master')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                标签管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">标签管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-2">
                    <div class="box box-success">
                        <div class="box-body">
                            <div id="tree">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            <div class="cb-toolbar">操作:</div>
                            <div class="btn-group margin-bottom">
                                <button class="btn btn-default btn-xs margin-r-5" id="btn_sort">排序</button>
                            </div>
                            <div class="btn-group margin-bottom pull-right">
                                <button type="button" class="btn btn-default btn-xs margin-r-5" id="query"
                                        data-toggle="modal" data-target="#modal_query">查询
                                </button>
                            </div>
                            <table id="table" data-toggle="table">
                                <thead>
                                <tr>
                                    <th data-field="id" data-width="45" data-align="center">ID</th>
                                    <th data-field="refer_id" data-width="60" data-align="center" data-align="center">
                                        关联ID
                                    </th>
                                    <th data-field="refer_type" data-width="90">关联类型</th>
                                    <th data-field="title">标题</th>
                                    <th data-field="clicks" data-width="60" data-align="center">访问量</th>
                                    <th data-field="username" data-width="60" data-align="center">操作员</th>
                                    <th data-field="created_at" data-width="130" data-align="center">创建时间</th>
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
                                        <input type="hidden" id="name">
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">标题:</label>
                                            <div class="col-sm-4">
                                                <input id="title" name="title" class="form-control" placeholder="">
                                            </div>
                                            <label class="col-sm-2 control-label">操作员:</label>
                                            <div class="col-sm-4">
                                                {!! Form::select('user_id', $users, 0, ['class' => 'form-control']) !!}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="start_date" class="control-label col-sm-2">开始日期:</label>
                                            <div class="col-sm-4">
                                                <div class="input-group date" id="start_date">
                                                    <input class="form-control" name="start_date" type="text"
                                                           id="start_date">
                                                    <span class="input-group-addon"> <span
                                                                class="glyphicon glyphicon-calendar"></span> </span>
                                                </div>
                                            </div>
                                            <label for="end_date" class="control-label col-sm-2">截止日期:</label>
                                            <div class="col-sm-4">
                                                <div class="input-group date" id="end_date">
                                                    <input class="form-control" name="end_date" type="text"
                                                           id="end_date">
                                                    <span class="input-group-addon"> <span
                                                                class="glyphicon glyphicon-calendar"></span> </span>
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
        $.ajax({
            type: 'get',
            async: false,
            url: '/admin/tags/tree',
            success: function (data) {
                $('#tree').treeview({
                    data: data,
                    showTags: true,
                    onNodeSelected: function (event, data) {
                        $('#name').val(data.text);
                        $('#table').bootstrapTable('refresh');
                    }
                });
                $('#tree').treeview('selectNode', [0, {silent: false}]);
            }
        });

        $('#table').bootstrapTable({
            method: 'get',
            url: '/admin/tags/table',
            pagination: true,
            pageNumber: 1,
            pageSize: 20,
            pageList: [10, 25, 50, 100],
            sidePagination: 'server',
            clickToSelect: true,
            striped: true,
            onLoadSuccess: function (data) {
                $('#btn_sort').removeClass('active');
                $('#btn_sort').text('排序');
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
                            url: '/admin/tags/sort',
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
                var object = $('#form_query input,#form_query select').serializeObject();
                object['name'] = $('#name').val();
                object['_token'] = '{{ csrf_token() }}';
                object['offset'] = params.offset;
                object['limit'] = params.limit;
                return object;
            },
        });

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
    </script>
@endsection