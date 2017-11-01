@extends('admin.layouts.master')

@section('content')
    <!-- X-editable (Select2) -->
    <link href="/plugins/x-editable/1.4.3/inputs/select2/lib/select2.css" rel="stylesheet">
    <script src="/plugins/x-editable/1.4.3/inputs/select2/lib/select2.js"></script>

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                系统管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">系统设置</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-info">
                        <div class="box-body">
                            <table id="table"
                                   data-toggle="table"
                                   data-url="options/table"
                                   data-pagination="true"
                                   data-search="true"
                                   data-show-refresh="true"
                                   data-show-toggle="true"
                                   data-show-columns="true"
                                   data-toolbar="#toolbar">
                                <thead>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>
        var date = new Date();
        var year = date.getFullYear();

        function save(params) {
            $.ajax({
                url: '/admin/options/' + params.pk + '/save',
                data: params,
                success: function () {
                    $('#table').bootstrapTable('refresh');
                },
                error: function () {
                    alert('Error');
                },
            });
        }

        $('#table').bootstrapTable({
            onLoadSuccess: function (options) {
                $(options.data).each(function (k, option) {
                    switch (option.type) {
                        case {{\App\Models\Option::TYPE_BOOLEAN}}:
                            $('.boolean').editable({
                                source: [
                                    {value: 0, text: '否'},
                                    {value: 1, text: '是'}
                                ],
                                url: function (params) {
                                    save(params)
                                },
                            });
                            break;
                        case {{\App\Models\Option::TYPE_TEXT}}:
                            $('.text').editable({
                                type: 'text',
                                url: function (params) {
                                    save(params)
                                },
                            });
                            break;
                        case {{\App\Models\Option::TYPE_TEXTAREA}}:
                            $('.textarea').editable({
                                showbuttons: 'bottom',
                                url: function (params) {
                                    save(params)
                                },
                            });
                            break;
                        case {{\App\Models\Option::TYPE_DATE}}:
                            $('.date').editable({
                                format: 'yyyy-mm-dd',
                                combodate: {
                                    maxYear: year,
                                },
                                url: function (params) {
                                    save(params)
                                },
                            });
                            break;
                        case {{\App\Models\Option::TYPE_DATETIME}}:
                            $('.datetime').editable({
                                placement: 'top',
                                combodate: {
                                    maxYear: year,
                                    firstItem: 'name'
                                },
                                url: function (params) {
                                    save(params)
                                },
                            });
                            break;
                        case {{\App\Models\Option::TYPE_SINGLE}}:
                            $('.single').editable({
                                source: [
                                    {value: 1, text: '男'},
                                    {value: 2, text: '女'}
                                ],
                                url: function (params) {
                                    save(params)
                                },
                            });
                            break;
                        case {{\App\Models\Option::TYPE_MULTIPLE}}:
                            $('.select').editable({
                                inputclass: 'input-large',
                                select2: {
                                    tags: ['英语', '汉语', '法语', '新西兰语'],
                                    width: '200px',
                                    tokenSeparators: [",", " "]
                                },
                                url: function (params) {
                                    save(params)
                                },
                            });
                            break;
                        default:
                            alert('Error');
                    }
                });
            },
            columns: [
                {
                    field: 'name',
                    title: '名称'
                }, {
                    field: 'value',
                    title: '值',
                    formatter: function (value, row, index) {
                        switch (row.type) {
                            case {{\App\Models\Option::TYPE_BOOLEAN}}:
                                return '<a href="javascript:void(0);" class="boolean" data-type="select" data-field="boolean" data-name="' + row.name + '" data-pk="' + row.id + '" data-value="' + row.value + '" data-title="布尔型"></a>';
                                break;
                            case {{\App\Models\Option::TYPE_TEXT}}:
                                return '<a href="javascript:void(0);" class="text" data-type="text" data-field="text" data-name="' + row.name + '" data-pk="' + row.id + '" data-title="文本">' + row.value + '</a>'
                                break;
                            case {{\App\Models\Option::TYPE_TEXTAREA}}:
                                return '<a href="javascript:void(0);" class="textarea" data-type="textarea" data-field="textarea" data-name="' + row.name + '" data-pk="' + row.id + '" data-placeholder="" data-title="多行文本">' + row.value + '</a>'
                                break;
                            case {{\App\Models\Option::TYPE_DATE}}:
                                return '<a href="javascript:void(0);" class="date" data-type="combodate" data-field="date" data-name="' + row.name + '" data-pk="' + row.id + '" data-value="' + row.value + '" data-format="YYYY-MM-DD" data-viewformat="YYYY-MM-DD" data-template="YYYY-MM-DD" data-title="日期"></a>';
                                break;
                            case {{\App\Models\Option::TYPE_DATETIME}}:
                                return '<a href="javascript:void(0);" class="datetime" data-type="combodate" data-field="datetime" data-name="' + row.name + '" data-pk="' + row.id + '" data-value="' + row.value + '" data-format="YYYY-MM-DD HH:mm" data-viewformat="YYYY-MM-DD HH:mm" data-template="YYYY-MM-DD HH:mm" data-title="日期时间"></a>'
                                break;
                            case {{\App\Models\Option::TYPE_SINGLE}}:
                                return '<a href="javascript:void(0);" class="single" data-type="select" data-field="single" data-name="' + row.name + '" data-pk="' + row.id + '" data-value="' + row.value + '" data-title="单选"></a>';
                                break;
                            case {{\App\Models\Option::TYPE_MULTIPLE}}:
                                return '<a href="javascript:void(0);" class="select" data-type="select2" data-field="select" data-name="' + row.name + '" data-pk="' + row.id + '" data-title="多选">' + row.value + '</a>'
                                break;
                            default:
                                alert('Error');
                        }
                    }
                }, {
                    field: "type_name",
                    title: "类型",
                }, {
                    field: "option",
                    title: "选项",
                }, {
                    field: 'site_name',
                    title: '站点'
                }
            ],
            onEditableSave: function (field, row, old, $el) {
                row._token = '{{ csrf_token() }}';
                $.ajax({
                    url: '/admin/options/' + row.id + '/save',
                    data: row,
                    success: function () {
                        $('#table').bootstrapTable('refresh');
                    },
                    error: function (data) {
                        alert('Error');
                    },
                });
            }
        });
    </script>
@endsection