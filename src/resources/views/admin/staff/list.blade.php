@extends('layouts.app')

@section('title', 'スタッフ一覧 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}">

@section('content')
    <div class="staff-list">
        <h1 class="page-title">スタッフ一覧</h1>
        <table class="staff-table">
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
            @foreach ($staffs as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td><a href="{{ route('admin.attendance.monthly', ['id' => $staff->id]) }}">詳細</a></td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
