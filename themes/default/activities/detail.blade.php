@extends('default.layout.master')

@section('title', $activity->title . ' - ' . $site->title)

@section('head')
    <link href="{{ asset('themes/default/css/detail.css') }}" rel="stylesheet">
    <script src="{{ asset('themes/default/js/detail.js') }}"></script>
    <!--JQuery-->
    <script src="/plugins/jquery/2.2.4/jquery.min.js"></script>
    <!--Bootstrap-->
    <link href="/plugins/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script src="/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="/css/mobile.css" type="text/css">
    <script src="/js/alert.js"></script>
@endsection

@section('body')
    @include('default.layout.header')

    <h4 class="act_title">{{ $activity->title }}</h4>
    <div class="E_voteTimes">
        <div class="pull-left">开始时间: <span>{{ date('Y-m-d H:i', strtotime($activity->start_time)) }}</span></div>
        <div class="pull-right">结束时间: <span>{{ date('Y-m-d H:i', strtotime($activity->end_time)) }}</span></div>
    </div>
    <div class="E_voteTimes">
        <div class="pull-left">作者: <span>{{ $activity->author }}</span></div>
        <div class="pull-right">来源: <span>{{ $activity->origin }}</span></div>
    </div>
    <div class="E_contBlock">
        {!! $activity->content !!}
    </div>

    @include('default.layout.footer')
@endsection

@section('js')
    <script src="{{ asset('/js/access.js') }}"></script>
@endsection