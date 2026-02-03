<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Eagles College') }}</title>

    @include('partials.login.inc_top')
</head>

<body>
<style>
    /* Eagles Logo Watermark */
    body::before {
        content: '';
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        width: 400px;
        height: 400px;
        background-image: url('{{ asset('global_assets/images/logo.png') }}');
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
        opacity: 0.05;
        z-index: -1;
        pointer-events: none;
    }
</style>
@include('partials.login.header')
@yield('content')
@include('partials.login.footer')

</body>

</html>
