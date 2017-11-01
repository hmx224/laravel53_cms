@extends('admin.layouts.master')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                个人信息
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li>个人信息</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <!-- right column -->
                <div class="col-md-12">
                    <!-- Horizontal Form -->
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            {!! Form::model($user,['method' => 'PATCH', 'class' => 'form-horizontal','action' => ['ProfileController@update', $user->id]]) !!}

                            <div class="form-group">
                                {!! Form::label('username', '用户名:',['class' => 'control-label col-sm-1']) !!}
                                <div class="col-sm-5">
                                    <h5>{!! $user->username !!}</h5>
                                </div>
                                {!! Form::label('name', '姓名:',['class' => 'control-label col-sm-1']) !!}
                                <div class="col-sm-5">
                                    {!! Form::text('name', null, ['class' => 'form-control', 'readonly']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('new', '登录密码:',['class' => 'control-label col-sm-1']) !!}
                                <div class="col-sm-5">
                                    {!! Form::password('new', ['class' => 'form-control' ,'placeholder'=>'若不修改请留空']) !!}
                                </div>
                                {!! Form::label('pwdConfirm', '确认登录密码:',['class' => 'control-label col-sm-1']) !!}
                                <div class="col-sm-5">
                                    {!! Form::password('pwdConfirm', ['class' => 'form-control' ,'placeholder'=>'若不修改请留空']) !!}
                                </div>
                            </div>

                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right">保　存</button>
                            </div>

                            {!! Form::close() !!}

                            @include('admin.errors.list')


                        </div>
                    </div><!-- /.box -->
                </div><!--/.col (right) -->
            </div>   <!-- /.row -->
        </section>
    </div>

@endsection