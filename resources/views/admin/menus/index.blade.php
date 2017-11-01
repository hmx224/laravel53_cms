@extends('admin.layouts.master')

@section('css')
    <style>
        .dd button[data-action=collapse] {
            display: none;
        }
    </style>
@endsection
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                菜单管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">菜单管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-3">
                    <div class="box box-success">
                        <div class="box-body">
                            <div id="tree">
                            </div>
                            {!! Form::open(['url' => '/admin/menus', 'method' => 'post', 'id' => 'form_create']) !!}
                            <div class="form-group">
                                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => '请输入菜单名称', 'required', 'id' => 'name']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::text('url', '#', ['class' => 'form-control', 'placeholder' => '请输入菜单URL', 'required', 'id' => 'url']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::text('permission', '', ['class' => 'form-control', 'placeholder' => '请输入权限编码', 'id' => 'url']) !!}
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    {!! Form::text('icon', 'fa-cube', ['class' => 'form-control', 'placeholder' => '请选择菜单图标', 'id' => 'icon']) !!}
                                    <span class="input-group-addon"></span>
                                </div>
                            </div>
                            <input type="submit" class="btn btn-info btn-block" value="添加到菜单">
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            <div class="dd" id="menu-nestable">
                                {!! \App\Helpers\HtmlBuilder::menuEditor($menus) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="modal fade common" id="modal_edit" tabindex="-1" role="dialog">
        <div class="modal-dialog" style="width:480px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                    <h4 class="modal-title">请输入菜单信息</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box box-info">
                                {!! Form::open(['url' => '/admin/menus', 'method' => 'post', 'id' => 'form_edit']) !!}
                                <input id="method" name="_method" type="hidden" value="PUT">
                                <div class="box-body">
                                    <div class="form-group">
                                        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => '请输入菜单名称', 'required']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::text('url', '#', ['class' => 'form-control', 'placeholder' => '请输入菜单URL', 'required']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::text('permission', '', ['class' => 'form-control', 'placeholder' => '请输入权限编码', 'id' => 'url']) !!}
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            {!! Form::text('icon', 'fa-cube', ['class' => 'form-control', 'placeholder' => '请选择菜单图标']) !!}
                                            <span class="input-group-addon"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button class="btn btn-default" data-dismiss="modal">取消</button>
                                    <button type="submit" class="btn btn-info pull-right">提交</button>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $.ajax({
            type: 'get',
            async: false,
            url: '/admin/menus/modules',
            success: function (data) {
                $('#tree').treeview({
                    data: data,
                    onNodeSelected: function (event, data) {
                        $('#form_create input[name=name]').val(data.text);
                        $('#form_create input[name=url]').val(data.url);
                        $('#form_create input[name=permission]').val(data.permission);
                        $('#form_create input[name=icon]').val(data.fa_icon);
                    }
                });
            }
        });

        $('input[name=icon]').iconpicker();

        $('.dd').nestable();

        $('.btn-menu-remove').click(function () {
            var id = $(this).data('id');
            $.ajax({
                url: '/admin/menus/' + id,
                method: 'post',
                data: {'_token': '{{ csrf_token() }}', '_method': 'delete'},
                success: function (data) {
                    window.location.reload();
                }
            });
        });

        $('.btn-menu-edit').click(function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var url = $(this).data('url');
            var permission = $(this).data('permission');
            var icon = $(this).data('icon');
            $('#form_edit').attr('action', '/admin/menus/' + id);
            $('#form_edit input[name=name]').val(name);
            $('#form_edit input[name=url]').val(url);
            $('#form_edit input[name=permission]').val(permission);
            $('#form_edit input[name=icon]').val(icon);
            $("#modal_edit").modal('show');
        });

        $('#menu-nestable').on('change', function () {
            var data = $('#menu-nestable').nestable('serialize');
            console.log(data);
            $.ajax({
                url: '/admin/menus/sort',
                method: 'post',
                data: {'_token': '{{ csrf_token() }}', 'data': data},
                success: function (data) {
                }
            });
        });
    </script>
@endsection
