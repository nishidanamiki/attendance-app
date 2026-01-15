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
        @php
            $isAdmin = auth()->check() && auth()->user()->is_admin;
            $homeUrl = $isAdmin ? route('admin.attendance.list') : route('attendance.index');
        @endphp
        <div class="header__inner">
            <div class="header__left">
                <a href="{{ $homeUrl }}">
                    <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH勤怠管理アプリのロゴ">
                </a>
            </div>
            <div class="header__right">
                @guest
                    {{-- メニューなし --}}
                @endguest
                @auth
                    @if ($isAdmin)
                        <nav class="header__nav">
                            <ul class="header__menu">
                                <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
                                <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
                                <li><a href="{{ route('admin.stamp_correction_request.list') }}">申請一覧</a></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button class="header__link-button" type="submit">ログアウト</button>
                                    </form>
                                </li>
                            </ul>
                        </nav>
                    @else
                        @php
                            $isAttendanceIndex = request()->routeIs('attendance.index');
                            $isDone = ($status ?? null) === 'DONE';
                        @endphp
                        <nav class="header__nav">
                            <ul class="header__menu">
                                @if ($isAttendanceIndex && $isDone)
                                    <li><a href="{{ route('attendance.list') }}">今月の出勤一覧</a></li>
                                    <li><a href="{{ route('stamp_correction_request.list') }}">申請一覧</a></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button class="header__link-button" type="submit">ログアウト</button>
                                        </form>
                                    </li>
                                @else
                                    <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                                    <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                                    <li><a href="{{ route('stamp_correction_request.list') }}">申請</a></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button class="header__link-button">ログアウト</button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    @endif
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
