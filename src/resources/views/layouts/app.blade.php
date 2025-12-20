<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'COACHTECH勤怠管理アプリ')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__left">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH勤怠管理アプリのロゴ">
                </a>
            </div>
        </div>
    </header>
    @yield('content')
</body>

</html>
