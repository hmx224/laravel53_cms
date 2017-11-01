@extends('admin.layouts.master')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                {{ $module->title }} - 字段管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li><a href="/admin/modules"> 模型管理</a></li>
                <li class="active">字段管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.confirm', ['message' => '您确认删除该字段吗？'])
                            @include('admin.layouts.flash')
                            @include('admin.modules.fields.form')
                            @include('admin.modules.fields.table')
                            @include('admin.modules.fields.script')
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection