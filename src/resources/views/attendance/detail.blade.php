@extends('layouts.app')

@section('title', '詳細画面 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
    <div class="detail-container">
        <h1 class="page-title">勤怠詳細</h1>
        <form action="{{ route('stamp_correction_request.store') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="work_date" value="{{ $date }}">
            <input type="hidden" name="attendance_id" value="{{ $attendance?->id ?? '' }}">
            @include('attendance.partials.detail_table')
            @if (!$pendingRequest)
                <button class="submit" type="submit">修正</button>
            @endif
            @if ($pendingRequest)
                <p class="notice">*承認待ちのため修正はできません。</p>
            @endif
        </form>
    </div>
@endsection
