<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{--        <title>系统</title>--}}
    <title>管理系统</title>

    <!-- Fonts -->
    {{--        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">--}}
{{--    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">--}}
    {{--        <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-adminE/font/raleway.css") }}">--}}

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            /*font-family: 'Raleway', sans-serif;*/
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 44px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    {{--@if (Route::has('login'))--}}
    {{--<div class="top-right links">--}}
    {{--@if (Auth::check())--}}
    {{--<a href="{{ url('/home') }}">Home</a>--}}
    {{--@else--}}
    {{--<a href="{{ url('/login') }}">Login</a>--}}
    {{--<a href="{{ url('/register') }}">Register</a>--}}
    {{--@endif--}}
    {{--</div>--}}
    {{--@endif--}}

    <div class="content">
        {{--                <div class="title m-b-md">--}}
        {{--                   系统--}}
        {{--                </div>--}}

        <div class="links">
            {{--                    <a href="https://mall-to.com">系统官网</a>--}}
            {{--                    <a href="https://wechat.mall-to.com">产品授权(微信公众号管理员授权)</a>--}}
            {{--                    @if(config('app.wiki'))<a target="_blank" href="{!! config('app.wiki') !!}">帮助文档</a>@endif--}}
            <a href="/admin">管理系统</a>
            {{--<a href="https://forge.laravel.com">Forge</a>--}}
            {{--<a href="https://github.com/laravel/laravel">GitHub</a>--}}
        </div>
    </div>
</div>
</body>
</html>
