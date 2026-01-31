@extends('layouts.app')

@section('title', '管理者・詳細画面 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
    <div class="detail-container">
        <h1 class="page-title">勤怠詳細</h1>
        <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST" novalidate>
            @csrf
            @method('PATCH')
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
            <input type="hidden" name="work_date" value="{{ $date ?? $attendance->work_date }}">
            @if ($pendingRequest)
                @include('attendance.partials.detail_table_text')
            @else
                @include('attendance.partials.detail_table_form')
            @endif
            @if (!$pendingRequest)
                <button class="submit" type="submit">修正</button>
            @endif
            @if ($pendingRequest)
                <p class="notice">*承認待ちのため修正はできません。</p>
            @endif
        </form>
    </div>
@endsection
