@extends('default.layout.master')

@section('title', $category->name . ' - ' . $site->title)

@section('head')
    <link href="{{ asset('themes/default/css/category.css') }}" rel="stylesheet">
    <script src="{{ asset('themes/default/js/category.js') }}"></script>
@endsection

@section('body')
    @include('default.layout.header')

    <h2>移动端</h2>
    <ul>
        @foreach($activities as $activity)
            <li><a href="{{ "detail-$activity->id.html" }}">{{ $activity->title }}</a></li>
        @endforeach
    </ul>

    @include('default.layout.footer')
@endsection

@section('js')

@endsection