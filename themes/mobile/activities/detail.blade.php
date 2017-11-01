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
        <div class="pull-left">开始时间: <span>{{ date('Y-m-d', strtotime($activity->start_time)) }}</span></div>
        <div class="pull-right">结束时间: <span>{{ date('Y-m-d', strtotime($activity->end_time)) }}</span></div>
    </div>
    <div class="E_voteTimes">
        <div class="pull-left">作者: <span>{{ $activity->author }}</span></div>
        <div class="pull-right">来源: <span>{{ $activity->origin }}</span></div>
    </div>
    <div class="E_contBlock">
        {!! $activity->content !!}
    </div>

    @if(isset($share) == 1)
    @else
        @if(isset($member)&&$activity->data->where('member_id', $member->id)->count() == 0)
            <div class="form-group">
                <input id="person_name" name="person_name" type="text" class="form-control" placeholder="请输入姓名">
            </div>
            <div class="form-group">
                <input id="person_mobile" name="person_mobile" type="text" class="form-control" placeholder="请输入手机号">
            </div>
            <input type="button" class="btn btn-danger btn-block sign" value="我要报名"
                   style="line-height:30px;font-size:16px;color:#FFFFFF"/>
        @else
            <input type="button" class="btn btn-danger btn-block sign disabled" value="您已报名"
                   style="line-height:30px;font-size:16px;color:#FFFFFF"/>
        @endif
    @endif
    <div style="height:60px"></div>

    @include('default.layout.footer')

@endsection

@section('js')
    <script src="{{ asset('/js/access.js') }}"></script>
    @if(isset($member))
        <script>
            $('.btn.sign').click(function () {
                var params = {
                    site_id: '{{ $activity->site_id }}',
                    activity_id: '{{ $activity->id }}',
                    member_id: '{{ $member->id }}',
                    person_name: $('#person_name').val(),
                    person_mobile: $('#person_mobile').val(),
                }

                var url = '{!! "/api/activities/commit?activity_id= $activity->id&token=$member->token" !!}';
                $.ajax({
                    type: 'get',
                    url: url,
                    data: params,
                    success: function (data) {
                        if (data.status_code == 200) {
                            $.fn.alert({
                                tip: '报名成功',
                                cancelCallback: function () {
                                    window.scrollTo(0, 0);
                                    window.location.href = '{!! "/api/activities/detail?id=$activity->id&token=$member->token" !!}'
                                }
                            });
                        }
                        else {
                            $.fn.alert({
                                tip: data.message,
                            });
                        }
                    },
                    error: function (data) {
                        alert(JSON.stringify(data.message));
                    }
                });

            });
        </script>
    @endif
@endsection