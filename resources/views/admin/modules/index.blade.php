@extends('admin.layouts.master')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                模块管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">模块管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            @include('admin.modules.toolbar')
                            @include('admin.modules.form')
                            @include('admin.modules.table')
                            @include('admin.modules.script')
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection