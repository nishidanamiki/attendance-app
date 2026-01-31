@extends('layouts.app')

@section('title', '申請詳細 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
    <div class="detail-container">
        <h1 class="page-title">勤怠詳細</h1>
        @include('attendance.partials.detail_table_text')
        @if ($stampRequest->status === 'pending')
            <p class="notice">*承認待ちのため修正はできません。</p>
        @endif
    </div>
@endsection
