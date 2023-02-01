@extends('layouts.app')

@section('content')
    {{ __('You are logged in!') }}
{{--@vite('resources/css/app.css')--}}

<div id="app"></div>

@vite('resources/js/app.js')

@endsection
