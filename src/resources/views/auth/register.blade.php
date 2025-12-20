@extends('layouts.app')

@section('title', '会員登録 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
    <div class="register-container">
        <h1 class="page-title">会員登録</h1>

        <section class="register-form">
            <h2 class="visually-hidden">会員登録フォーム</h2>
            <form action="" method="POST" novalidate>
                @csrf
                <div class="form-group">
                    <label for="name">名前</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    <div class="form__error">
                        @error('name')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
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
                <div class="form-group">
                    <label for="password_confirmation">パスワード確認</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                    <div class="form__error">
                        @error('password_confirmation')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="form-button">
                    <button class="form-button__submit">登録する</button>
                </div>
            </form>
        </section>
        <section class="login-section">
            <h2 class="visually-hidden">ログインリンク</h2>
            <a class="login-link" href="{{ route('login') }}">ログインはこちら</a>
        </section>
    </div>
@endsection
