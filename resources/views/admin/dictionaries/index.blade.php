@extends('admin.layouts.master')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                字典管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">字典管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-3">
                    <div class="box box-success">
                        <div class="box-body">
                            <div id="tree"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.confirm', ['message' => '您确认删除该条信息吗？'])
                            @include('admin.layouts.flash')
                            @include('admin.dictionaries.toolbar')
                            @include('admin.dictionaries.form')
                            @include('admin.dictionaries.table')
                            @include('admin.dictionaries.script')
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection