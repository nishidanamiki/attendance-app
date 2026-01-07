@extends('layouts.app')

@section('title', '勤怠一覧 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')
    @php
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

        $fmt = function ($sec) {
            $h = floor($sec / 3600);
            $m = floor(($sec % 3600) / 60);
            return sprintf('%d:%02d', $h, $m);
        };
    @endphp

    <div class="attendance-list">
        <h1 class="page-title">勤怠一覧</h1>
        <div class="month-nav">
            <a href="{{ route('attendance.list', ['month' => $prevMonth->format('Y-m')]) }}"><span
                    class="light-gray">←</span>前月</a>
            <div class="month-nav__center">
                <img src="{{ asset('images/icons/カレンダーアイコン8.svg') }}" alt="カレンダーアイコン">
                <p>{{ $currentMonth->format('Y/m') }}</p>
            </div>
            <a href="{{ route('attendance.list', ['month' => $nextMonth->format('Y-m')]) }}">翌月<span
                    class="light-gray">→</span></a>
        </div>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($days as $day)
                    @php
                        $dateKey = $day->toDateString();
                        $attendance = $attendances->get($dateKey);
                        $weekday = $weekdays[$day->dayOfWeek];

                        $breakSeconds = 0;
                        $workSeconds = null;

                        if ($attendance) {
                            $breakSeconds = $attendance->breakTimes->sum(function ($bt) {
                                if (!$bt->break_out_at) {
                                    return 0;
                                }

                                return \Carbon\Carbon::parse($bt->break_in_at)->diffInSeconds(
                                    \Carbon\Carbon::parse($bt->break_out_at),
                                );
                            });

                            if ($attendance->clock_in_at && $attendance->clock_out_at) {
                                $total = \Carbon\Carbon::parse($attendance->clock_in_at)->diffInSeconds(
                                    \Carbon\Carbon::parse($attendance->clock_out_at),
                                );

                                $workSeconds = max(0, $total - $breakSeconds);
                            }
                        }
                    @endphp

                    <tr>
                        <td>{{ $day->format('m/d') }} ({{ $weekday }})</td>
                        <td>{{ $attendance && $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '' }}
                        </td>
                        <td> {{ $attendance && $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '' }}
                        </td>
                        <td>{{ $attendance ? $fmt($breakSeconds) : '' }}</td>
                        <td>{{ $workSeconds !== null ? $fmt($workSeconds) : '' }}</td>
                        <td>
                            <a href="{{ route('attendance.openByDate', ['date' => $dateKey]) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
