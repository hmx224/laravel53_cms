@extends('admin.layouts.master')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                栏目管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">栏目管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-4">
                    <div class="box box-success">
                        <div class="box-body">
                            <div id="tree"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="box box-info">
                        <div class="box-header">
                            <button class="btn btn-primary btn-xs margin-r-5 margin-t-5" onclick="create()"> 新增子栏目</button>
                        </div>
                        <div class="box-body">
                            @include('admin.layouts.confirm', ['message' => '您确认删除该条信息吗？'])
                            @include('admin.layouts.flash')
                            @include('admin.layouts.modal', ['id' => 'modal_create'])
                            <form id="form_query" class="form-horizontal">
                                <input type="hidden" id="category_id" name="category_id" value="">
                            </form>
                            <table id="table"
                                   data-toggle="table"
                                   data-url="/admin/categories/table">
                                <thead>
                                <tr>
                                    <th data-field="id" data-align="center" data-width="45">ID</th>
                                    <th data-field="code" data-width="90">编码</th>
                                    <th data-field="name">栏目名称</th>
                                    <th data-field="module_title" data-align="center" data-width="90">模块</th>
                                    <th data-field="sort" data-align="center" data-width="60" data-editable="true">序号</th>
                                    <th data-field="state_name" data-align="center" data-width="90" data-formatter="stateFormatter">状态</th>
                                    <th data-field="action" data-align="center" data-width="70" data-formatter="actionFormatter" data-events="actionEvents"> 操作
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
        $.getJSON('/admin/categories/tree', function (data) {
            $('#tree').treeview({
                showTags: true,
                searchResultColor: 'white',
                data: data,
                levels: 3,
                onNodeSelected: function (event, data) {
                    $('#category_id').val(data.id);
                    $('#table').bootstrapTable('refresh');
                }
            });
            if (getNodeIndex(parseInt(getQueryString('category_id')), data) >= 0) {
                $('#tree').treeview('selectNode', [nodeIndex, {silent: false}]);
            }
            else {
                $('#tree').treeview('selectNode', [0, {silent: false}]);
            }
        });

        $('#table').bootstrapTable({
            onEditableSave: function (field, row, old, $el) {
                $.ajax({
                    url: "/admin/categories/" + row.id + '/save',
                    data: {'_token': '{{ csrf_token() }}', 'sort': row.sort},
                    success: function (data, status) {
                    },
                    error: function (data) {
                        alert('Error');
                    }
                });
            },
            queryParams: function (params) {
                var object = $('#form_query input,#form_query select').serializeObject();
                object['offset'] = params.offset;
                object['limit'] = params.limit;
                return object;
            },
        });

        $('#table').on('all.bs.table', function (e, name, args) {
            $(window).resize();
        });

        function actionFormatter(value, row, index) {
            return [
                '<button class="btn btn-primary btn-xs edit" data-toggle="modal" data-target="#modal_edit">' +
                '<i class="fa fa-edit" data-toggle="tooltip" data-placement="left" title="编辑" ></i></button>',
                '<span> </span>',
                '<button class="btn btn-danger btn-xs remove" data-toggle="modal" data-target="#modal"><i class="fa fa-trash"></i></button>'
            ].join('');
        }

        $("#modal_remove").click(function () {
            var row_id = $(this).data('id');
            $.ajax({
                url: '/admin/categories/' + row_id + '/delete',
                data: {'_token': '{{ csrf_token() }}'},
                success: function (data) {
                    window.location.reload();
                }
            });
        });

        window.actionEvents = {
            'click .edit': function (e, value, row, index) {
                window.location.href = '/admin/categories/' + row.id + '/edit';
            },
            'click .remove': function (e, value, row, index) {
                $('#modal_remove').data('id', row.id);
            },
        };

        function stateFormatter(value, row, index) {
            var style = 'label-primary';
            switch (row.state_name) {
                case '已启用':
                    style = 'label-success';
                    break;
                case '已禁用':
                    style = 'label-danger';
                    break;
            }
            return [
                '<span class="label ' + style + '">' + row.state_name + '</span>',
            ].join('');
        }

        function create() {
            window.location.href = '/admin/categories/create/' + $('#category_id').val();
        }

    </script>
@endsection