@extends('layouts.app')

@section('title', '管理者ログイン - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}">
@endsection

@section('content')
    <div class="admin-login">
        <h1 class="page title">管理者ログイン</h1>
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
                <button class="form-button__submit">管理者ログインする</button>
            </div>
        </form>
    </div>
@endsection
