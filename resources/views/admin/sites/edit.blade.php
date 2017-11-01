@extends('admin.layouts.master')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                修改站点
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li><a href="#">系统管理</a></li>
                <li><a href="/sites">站点管理</a></li>
                <li class="active">编辑</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <!-- right column -->
                <div class="col-md-12">
                    <!-- Horizontal Form -->
                    <div class="box box-info">
                        <div class="box-body">

                            {!! Form::model($site,['method' => 'PATCH', 'class' => 'form-horizontal','action' => ['SiteController@update', $site->id]]) !!}

                            @include('admin.sites._form')

                            {!! Form::close() !!}

                            @include('admin.errors.list')

                        </div>
                    </div><!-- /.box -->
                </div><!--/.col (right) -->
            </div>   <!-- /.row -->
        </section>
    </div>

@endsection