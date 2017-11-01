@extends('default.layout.master')

@section('title', $article->title . ' - ' . $site->title)

@section('head')
    <link href="{{ asset('themes/default/css/detail.css') }}" rel="stylesheet">
    <script src="{{ asset('themes/default/js/detail.js') }}"></script>
@endsection

@section('body')
    @include('default.layout.header')

    <h2>移动端</h2>
    <h2>{{ $article->title }}</h2>
    <div>
        {!! $article->content !!}
    </div>

    @include('default.layout.footer')
@endsection

@section('js')
    <script src="{{ asset('/js/access.js') }}"></script>
@endsection