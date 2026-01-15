@extends('layouts.app')

@section('title', 'ログイン - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
    <div class="login-container">
        <h1 class="page title">ログイン</h1>

        <section class="login-form">
            <h2 class="visually-hidden">ログインフォーム</h2>
            <form action="{{ route('login') }}" method="POST" novalidate>
                @csrf
                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    <div class="form__error">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input type="password" id="password" name="password" required>
                    <div class="form__error">
                        @error('password')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="form-button">
                    <button class="form-button__submit">ログインする</button>
                </div>
            </form>
        </section>
        <section class="register-section">
            <h2 class="visually-hidden">会員登録リンク</h2>
            <a class="register-link" href="{{ route('register') }}">会員登録はこちら</a>
        </section>
    </div>
@endsection
