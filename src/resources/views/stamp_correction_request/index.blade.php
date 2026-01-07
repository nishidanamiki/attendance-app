@extends('layouts.app')

@section('title', '申請一覧 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/stamp_correction_request/index.css') }}">
@endsection

@section('content')
    <div class="request-list">
        <h1 class="page-title">申請一覧</h1>
        <nav class="tab-menu">
            <a href="#" class="tab">承認待ち</a>
            <a href="#" class="tab">承認済み</a>
        </nav>
        <table>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日付</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
            <tr>
                <td>承認待ち</td>
                <td>西伶奈</td>
                <td>2023/06/01</td>
                <td>遅延のため</td>
                <td>2023/06/02</td>
                <td>詳細</td>
            </tr>
        </table>
    </div>
@endsection
