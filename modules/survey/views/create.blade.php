@extends('admin.layouts.master')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                添加{{ $module->title }}
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">{{ $module->title }}管理</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.errors.list')
                            {!! Form::open(['url' => '/admin/surveys', 'class' => 'form-horizontal']) !!}

                            @include('survey.views._form')

                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </section>


    </div>

@endsection

