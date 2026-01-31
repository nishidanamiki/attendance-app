@extends('layouts.app')

@section('title', '管理者・スタッフ別勤怠一覧 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')
    @php
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();
    @endphp

    <div class="attendance-list">
        <h1 class="page-title">{{ $targetUser->name }}さんの勤怠</h1>
        <div class="month-nav">
            <a
                href="{{ route('admin.attendance.monthly', ['id' => $targetUser->id, 'month' => $prevMonth->format('Y-m')]) }}">
                <span class="light-gray">←</span>前月
            </a>
            <div class="month-nav__center">
                <img src="{{ asset('images/icons/calendar_icon_08.svg') }}" alt="カレンダーアイコン">
                <p>{{ $currentMonth->format('Y/m') }}</p>
            </div>
            <a
                href="{{ route('admin.attendance.monthly', ['id' => $targetUser->id, 'month' => $nextMonth->format('Y-m')]) }}">
                翌月<span class="light-gray">→</span>
            </a>
        </div>

        @include('attendance.partials.monthly_table', [
            'days' => $days,
            'attendances' => $attendances,
            'isAdmin' => true,
            'targetUser' => $targetUser,
        ])
        <div class="attendance-actions">
            <a class="csv-button"
                href="{{ route('admin.attendance.monthly', [
                    'id' => $targetUser->id,
                    'month' => $currentMonth->format('Y-m'),
                    'csv' => 1,
                ]) }}">
                CSV出力
            </a>
        </div>
    </div>
@endsection
