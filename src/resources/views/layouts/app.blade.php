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
                <a href="{{ url('/attendance') }}">
                    <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH勤怠管理アプリのロゴ">
                </a>
            </div>
            <div class="header__right">
                @guest
                    {{-- メニューなし --}}
                @endguest

                @auth
                    @php
                        $isAttendanceIndex = request()->routeIs('attendance.index');
                        $isDone = isset($status) && $status === 'DONE';
                    @endphp
                    <nav class="header__nav">
                        <ul class="header__menu">
                            @if ($isAttendanceIndex && $isDone)
                                <li><a href="{{ route('attendance.list') }}">今月の出勤一覧</a></li>
                                <li><a href="{{ route('requests.index') }}">申請一覧</a></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button class="header__link-button" type="submit">ログアウト</button>
                                    </form>
                                </li>
                            @else
                                <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                                <li><a href="/attendance/list">勤怠一覧</a></li>
                                <li><a href="/stamp_correction_request/list">申請</a></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button class="header__link-button">ログアウト</button>
                                    </form>
                                </li>
                            @endif
                        </ul>
                    </nav>
                @endauth
            </div>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    @yield('script')
</body>

</html>
