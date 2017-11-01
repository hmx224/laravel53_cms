@extends('admin.layouts.master')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                {{ $module->title }}管理
            </h1>
            <ol class="breadcrumb">
                <li><a href="/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">{{ $module->title }}管理</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-2">
                    <div class="box box-success">
                        <div class="box-body">
                            <div id="tree">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="box box-info">
                        <div class="box-body">
                            @include('admin.layouts.flash')
                            @include('admin.layouts.confirm', ['message' => '您确认删除该条信息吗？'])
                            @include('admin.layouts.modal', ['id' => 'modal_comment'])
                            @include('video.views.toolbar')
                            @include('video.views.script')
                            @include('video.views.query')
                            @include('admin.contents.push')
                            @if($total)
                                <div id="videos" class="row list-group">
                                    @foreach($videos as $video)
                                        <div class="item col-xs-2 col-lg-2 margin-bottom action">
                                            <div class="thumbnail margin-bottom">
                                                <img class="group list-group-image"
                                                     style="width:189px; height:154px"
                                                     src="{{ !empty($video->image_url) ? $video->image_url:'/images/video_default.jpg' }} "
                                                     alt="缩略图"/>
                                                <div style="margin-top:10px;">
                                                    <p class="info-box-text">

                                                        @if($video->getStateName($video->state) == Modules\Video\Models\Video::STATES[0])
                                                            <button class='btn btn-xs btn-danger'>{{ Modules\Video\Models\Video::STATES[0] }}</button>

                                                        @elseif($video->getStateName($video->state) == Modules\Video\Models\Video::STATES[1])
                                                            <button class='btn btn-xs btn-primary'>{{ Modules\Video\Models\Video::STATES[1] }}</button>

                                                        @elseif($video->getStateName($video->state) == Modules\Video\Models\Video::STATES[2])
                                                            <button class='btn btn-xs btn-warning'>{{ Modules\Video\Models\Video::STATES[2] }}</button>

                                                        @elseif($video->getStateName($video->state) == Modules\Video\Models\Video::STATES[9])
                                                            <button class='btn btn-xs btn-success'>{{ Modules\Video\Models\Video::STATES[9] }}</button>
                                                        @endif

                                                        @foreach($video->tags as $tags)
                                                            <span class="{{  !empty($tags) ? 'badge badge-default pull-right ' :''}}tag_{{$video->id}}">
                                                            {{ !empty($tags) ? $tags->name : null}}
                                                        </span>
                                                        @endforeach

                                                        <span class="{{ $video->top == Modules\Video\Models\Video::TOP_TRUE ? 'badge badge-default pull-right ' :''}}top_{{$video->id}}">
                                                            {{$video->top == Modules\Video\Models\Video::TOP_TRUE ? Modules\Video\Models\Video::TOP : null  }}
                                                        </span>
                                                    </p>
                                                    <p class="info-box-text">
                                                        <label style="cursor: pointer">
                                                            <input type="checkbox" class="check"
                                                                   value="{{$video->id}}">
                                                            {{ $video->title }}
                                                        </label>

                                                    </p>

                                                    <p class="info-box-text">
                                                        <span class="pull-left">发布时间:</span>
                                                        <span class="pull-right">{{ $video->published_at }}</span>
                                                    </p>

                                                    <p class="group inner list-group-item-text">
                                                        <label for="">id:{{ $video->id }}</label>
                                                        <span class="pull-right"><label
                                                                    for="">操作员:{{$video->user->name}}</label></span>
                                                    </p>
                                                </div>
                                                <div class="group inner list-group-item-text" style="text-align: center;">
                                                    <button class="btn btn-primary btn-sm   margin-r-10  edit"
                                                            onclick="edit_video({{ $video->id }})"
                                                            data-toggle="tooltip"
                                                            data-placement="top" title="编辑">
                                                        <i class="fa fa-edit">
                                                        </i>
                                                    </button>

                                                    <button class="btn btn-primary btn-sm margin-r-10  top"
                                                            onclick="top_video({{ $video->id }})">
                                                        @if($video->top == Modules\Video\Models\Video::TOP_TRUE)
                                                            <i class="fa fa-chevron-circle-down"
                                                               title="取消置顶"
                                                               data-placement="top"
                                                               data-toggle="tooltip">
                                                            </i>
                                                        @else
                                                            <i class="fa fa-chevron-circle-up"
                                                               title="置顶"
                                                               data-placement="top"
                                                               data-toggle="tooltip">
                                                            </i>
                                                        @endif
                                                    </button>

                                                    <button class="btn btn-primary btn-sm margin-r-10  tag"
                                                            data-toggle="tooltip"
                                                            onclick="tag_video({{ $video->id }})"
                                                            data-placement="top" title="推荐">
                                                        <i class="fa fa-hand-o-right">
                                                        </i>
                                                    </button>

                                                    <button class="btn btn-info btn-sm margin-r-10  comment"
                                                            data-toggle="modal"
                                                            data-target="#modal_comment"
                                                            onclick="comment_video({{ $video->id }})">
                                                        <i class="fa fa-comment" data-toggle="tooltip"
                                                           data-placement="top" title="查看评论">
                                                        </i>
                                                    </button>

                                                    <button class="btn btn-info btn-sm {{$video->id}}_push margin-r-10 "
                                                            data-toggle="modal"
                                                            data-target="#modal_push"
                                                            value="{{$video->title}}"
                                                            onclick="push_video({{$video->id}})">
                                                        <i class="fa fa-envelope"
                                                           data-toggle="tooltip"
                                                           data-placement="top" title="推送">
                                                        </i>
                                                    </button>

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="fixed-table-pagination" style="display: block;">
                                    <div class="pull-left pagination-detail">
                                        @if($videos->total() <= Modules\Video\Models\Video::PAGE_NUM)
                                            <span class="pagination-info">显示第 1 到第 {{ $total }}
                                                条记录，总共 {{ $total }}
                                                条记录</span>
                                        @else
                                            <span class="pagination-info">显示第 1 到第 {{ \Modules\Video\Models\Video::PAGE_NUM }}
                                                条记录，总共 {{ $total }}
                                                条记录</span>
                                        @endif
                                    </div>
                                    <div class="pull-right pagination">
                                        {{ $videos->links() }}
                                    </div>
                                </div>
                            @else
                                <div class="list-group">
                                    <div class="thumbnail margin-bottom">
                                        <div style="text-align:center;">没有找到匹配的记录</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

<script>
    {{--$.ajax({--}}
    {{--url: '{{$base_url}}/table',--}}
    {{--type: 'get',--}}
    {{--data: {'_token': '{{ csrf_token() }}'},--}}
    {{--success: function (data) {--}}
    {{--alert(data);--}}

    {{--var video_list = JSON.parse(data)['data'];--}}
    {{--data(data);--}}
    {{--$('#videos').html('');--}}
    {{--var html = '';--}}
    {{--for (var i = 0; i <= video_list.length; i++) {--}}
    {{--console.log(video_list[i]);--}}
    {{--html += ' <div class="item col-md-3 margin-bottom action">' +--}}
    {{--'<div class="thumbnail margin-bottom">' +--}}
    {{--'<img class="group list-group-image" style="width:303px; height:154px" src="" alt="缩略图"' +--}}
    {{--'<div class="caption">' +--}}
    {{--'<p class="info-box-text">' +--}}

    {{--@if($video->getStateName($video->state) == Modules\Video\Models\Video::STATES[0])--}}
    {{--<button class='btn btn-xs btn-danger'>{{ Modules\Video\Models\Video::STATES[0] }}</button>--}}

    {{--@elseif($video->getStateName($video->state) == Modules\Video\Models\Video::STATES[1])--}}
    {{--<button class='btn btn-xs btn-primary'>{{ Modules\Video\Models\Video::STATES[1] }}</button>--}}

    {{--@elseif($video->getStateName($video->state) == Modules\Video\Models\Video::STATES[2])--}}
    {{--<button class='btn btn-xs btn-warning'>{{ Modules\Video\Models\Video::STATES[2] }}</button>--}}

    {{--@elseif($video->getStateName($video->state) == Modules\Video\Models\Video::STATES[9])--}}
    {{--<button class='btn btn-xs btn-success'>{{ Modules\Video\Models\Video::STATES[9] }}</button>--}}
    {{--@endif--}}

    {{--@foreach($video->tags as $tags)--}}
    {{--<span class="{{  !empty($tags) ? 'badge badge-default ' :''}}tag_{{$video->id}}">--}}
    {{--{{ !empty($tags) ? $tags->name : null}}--}}
    {{--</span>--}}
    {{--@endforeach--}}

    {{--'<span class="badge badge-default">' +--}}
    {{--'</span>' +--}}
    {{--'</p>' +--}}
    {{--'<p class="info-box-text">' +--}}
    {{--' <label style="cursor: pointer">' +--}}
    {{--'<input type="checkbox" class="check" value="">' +--}}
    {{--'</label>' +--}}
    {{--'<span class="pull-right"></span>' +--}}
    {{--'</p>' +--}}

    {{--' <p class="group inner list-group-item-text">' +--}}
    {{--'<label for="">id:</label>' +--}}
    {{--'<span class="pull-right"><label for="">操作员:</label></span>' +--}}
    {{--'</p>' +--}}

    {{--'<div class="row">' +--}}
    {{--' <div class="width-10p col-md-2 pull-left padding-r-5">' +--}}
    {{--' <button class="btn btn-primary btn-sm margin-r-5 edit" onclick="edit_video()" data-toggle="tooltip" ' +--}}
    {{--'data-placement="top" title="编辑">' +--}}
    {{--' <i class="fa fa-edit" style="width:23px">' +--}}
    {{--'  </i>' +--}}
    {{--' </button>' +--}}
    {{--' </div>' +--}}


    {{--' <div class="width-10p col-md-2 pull-left padding-r-5">' +--}}
    {{--'  <button class="btn btn-info btn-sm  margin-r-5" data-toggle="modal" data-target="#modal_push"' +--}}
    {{--' value="" onclick="push_video()">' +--}}
    {{--'<i class="fa fa-envelope" data-toggle="tooltip" data-placement="top" title="推送" style="width:23px">' +--}}
    {{--'  </i>' +--}}
    {{--'  </button>' +--}}
    {{--'  </div>' +--}}
    {{--'   </div>' +--}}
    {{--'    </div>' +--}}
    {{--'     </div>' +--}}
    {{--'     </div>';--}}
    {{--}--}}
    {{--},--}}
    {{--error: function () {--}}
    {{--toast('error', '操作失败');--}}
    {{--}--}}
    {{--})--}}


</script>