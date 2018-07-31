<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Museum of Spain') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @yield('header-script')
</head>
<body>
    <div id="app">
        @include ('partials.nav')
        <div class="wrapper">
            {{--@auth--}}
            <left-menu></left-menu>
            {{--<!-- <right-menu></right-menu> -->--}}
            {{--@endauth--}}
            @yield('content')
        </div>

    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- polymer dependencies -->
    <link rel="import" href="{{ url('js/bower_components/polymer/index.html') }}">
    <link rel="import" href="{{ url('js/elements/polymer-terminal.html') }}">

    @yield('page-script')
</body>
</html>
