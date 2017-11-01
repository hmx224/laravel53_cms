@extends('admin.layouts.master')
@section('content')
    <script src="{{ url('plugins/ace/1.2.7/ace.js') }}"></script>
    <script src="{{ url('plugins/ace/1.2.7/ext-language_tools.js') }}"></script>
    <script src="{{ url('plugins/ace/1.2.7/ext-emmet.js') }}"></script>
    <script src="{{ url('plugins/ace/1.2.7/emmet-core/emmet.js') }}"></script>
    <script src="{{ url('plugins/ace/1.2.7/theme-monokai.js') }}"></script>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                主题管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">主题管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    <div class="box box-success">
                        <div class="box-header ui-sortable-handle">
                            <h3 class="box-title"></h3>
                            <div class="box-tools pull-right">
                                <button class="btn btn-success btn-xs margin-r-5 margin-t-5" id="btn_create_theme" data-toggle="modal" data-target="#modal_theme"><i class="fa fa-plus"></i> 添加主题</button>
                                <button class="btn btn-info btn-xs margin-r-5 margin-t-5" id="btn_edit_theme"><i class="fa fa-edit"></i> 编辑主题</button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div id="tree">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="box box-info">
                        <div class="box-header ui-sortable-handle">
                            <h3 class="box-title margin-l-5"></h3>
                            <div class="box-tools">
                                <div class="btn-group" id="btn_create_file">
                                    <button type="button" class="btn btn-info btn-xs margin-t-5" data-toggle="modal" data-target="#modal_file">添加文件</button>
                                    <button type="button" class="btn btn-info btn-xs margin-r-5 margin-t-5 dropdown-toggle" data-toggle="dropdown">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="javascript:void(0)" onclick="createFile('index')">列表页 - index.blade.php</a></li>
                                        <li><a href="javascript:void(0)" onclick="createFile('detail')">详情页 - detail.blade.php</a></li>
                                    </ul>
                                </div>

                                <button class="btn btn-danger btn-xs margin-r-5 margin-t-5" id="btn_remove_file">删除文件</button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-info btn-xs margin-t-5">变量列表</button>
                                    <button type="button" class="btn btn-info btn-xs margin-r-5 margin-t-5 dropdown-toggle" data-toggle="dropdown">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu" id="list_var">
                                    </ul>
                                </div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-info btn-xs margin-t-5">代码片段</button>
                                    <button type="button" class="btn btn-info btn-xs margin-r-5 margin-t-5 dropdown-toggle" data-toggle="dropdown">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        @foreach ($snippets as $key => $snippet)
                                            <li><a href="javascript:void(0)" class="code" data-code="{{ $snippet }}">{{ $key }}</a></li>
                                        @endforeach
                                        <li class="divider"></li>
                                        <li><a href="http://laravelacademy.org/post/5919.html" target="_blank">参考文档</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            @include('admin.themes.form')
                            <pre id="editor" style="min-height:600px;"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('js')
    @include('admin.themes.script')
@endsection