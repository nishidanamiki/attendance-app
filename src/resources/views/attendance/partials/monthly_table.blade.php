@php
    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

    $formatSecondsToTime = function ($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf('%d:%02d', $hours, $minutes);
    };
@endphp

<table class="attendance-table">
    <tr>
        <th>日付</th>
        <th>出勤</th>
        <th>退勤</th>
        <th>休憩</th>
        <th>合計</th>
        <th>詳細</th>
    </tr>
    @foreach ($days as $day)
        @php
            $dateKey = $day->toDateString();
            $attendance = $attendances->get($dateKey);
            $weekday = $weekdays[$day->dayOfWeek];

            $breakSeconds = 0;
            $workSeconds = null;

            if ($attendance) {
                $breakSeconds = $attendance->breakTimes->sum(function ($breakTime) {
                    if (!$breakTime->break_out_at) {
                        return 0;
                    }

                    return \Carbon\Carbon::parse($breakTime->break_in_at)->diffInSeconds(
                        \Carbon\Carbon::parse($breakTime->break_out_at),
                    );
                });

                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                    $totalSeconds = \Carbon\Carbon::parse($attendance->clock_in_at)->diffInSeconds(
                        \Carbon\Carbon::parse($attendance->clock_out_at),
                    );

                    $workSeconds = max(0, $totalSeconds - $breakSeconds);
                }
            }
        @endphp

        <tr>
            <td>{{ $day->format('m/d') }} ({{ $weekday }})</td>
            <td>{{ $attendance && $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '' }}
            </td>
            <td> {{ $attendance && $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '' }}
            </td>
            <td>{{ $attendance ? $formatSecondsToTime($breakSeconds) : '' }}</td>
            <td>{{ $workSeconds !== null ? $formatSecondsToTime($workSeconds) : '' }}</td>
            <td>
                @if ($attendance)
                    @if ($isAdmin)
                        <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}">詳細</a>
                    @else
                        <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a>
                    @endif
                @else
                    @if ($isAdmin)
                        <a
                            href="{{ route('attendance.openByDate', [
                                'date' => $dateKey,
                                'user_id' => $targetUser->id,
                            ]) }}">詳細</a>
                    @else
                        <a href="{{ route('attendance.openByDate', ['date' => $dateKey]) }}">詳細</a>
                    @endif
                @endif
            </td>
        </tr>
    @endforeach
</table>
