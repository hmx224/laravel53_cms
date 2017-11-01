@extends('admin.layouts.master')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                编辑{{ $module->title }}
            </h1>
            <ol class="breadcrumb">
                <li><a href="/admin/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">编辑{{ $module->title }}</li>
            </ol>
        </section>

        <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-info">
                            <div class="box-body">

                                @include('admin.errors.list')

                                {!! Form::model($vote,['method' => 'PUT', 'action' => ['\Modules\Vote\Web\VoteController@update', $vote->id],
                                'class' => 'form-horizontal']) !!}

                                @include('vote.views._form')

                                {!! Form::close() !!}

                            </div>
                        </div>
                    </div>
                </div>
        </section>
    </div>

@endsection
