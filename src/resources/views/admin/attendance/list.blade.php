@extends('layouts.app')

@section('title', '管理者・勤怠一覧 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">

@section('content')
    <div class="list-container">
        <h1 class="page-title">{{ \Carbon\Carbon::parse($targetDate)->format('Y年n月j日') }}の勤怠</h1>
        <div class="date-nav">
            <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}"><span class="light-gray">←</span> 前日</a>
            <div class="date-nav__center">
                <img src="{{ asset('images/icons/calendar_icon_08.svg') }}" alt="カレンダーアイコン">
                <p>{{ \Carbon\Carbon::parse($targetDate)->format('Y/m/d') }}</p>
            </div>
            <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">翌日 <span class="light-gray">→</span></a>
        </div>
        @php
            function minutes_to_hm($minutes)
            {
                $hours = intdiv($minutes, 60);
                $mins = $minutes % 60;
                return sprintf('%d:%02d', $hours, $mins);
            }
        @endphp
        <table class="attendees-table">
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>
                    <td>
                        {{ $attendance->clock_in_at ? \Illuminate\Support\Str::of($attendance->clock_in_at)->limit(5, '') : '-' }}
                    </td>
                    <td>
                        {{ $attendance->clock_out_at ? \Illuminate\Support\Str::of($attendance->clock_out_at)->limit(5, '') : '-' }}
                    </td>
                    <td>{{ minutes_to_hm($attendance->break_minutes) }}</td>
                    <td>{{ minutes_to_hm($attendance->net_minutes) }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">この日に登録された勤怠はありません。</td>
                </tr>
            @endforelse
        </table>
    </div>
@endsection
