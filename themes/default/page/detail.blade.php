@extends('default.layout.master')

@section('title', $page->title . ' - ' . $site->title)

@section('head')
    <link href="{{ asset('themes/default/css/detail.css') }}" rel="stylesheet">
    <script src="{{ asset('themes/default/js/detail.js') }}"></script>
@endsection

@section('body')
    @include('default.layout.header')

    <h2>{{ $page->title }}</h2>
    <div>
        {!! $page->content !!}
    </div>

    @include('default.layout.footer')
@endsection

@section('js')
    <script src="{{ asset('/js/access.js') }}"></script>
@endsection