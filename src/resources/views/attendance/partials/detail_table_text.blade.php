@php
    $displayDate = $displayDate ?? ($date ?? ($attendance->work_date ?? null));
    $userForDisplay = $userForDisplay ?? ($attendance->user ?? auth()->user());
@endphp

<table class="detail-table">
    <tr>
        <th>名前</th>
        <td>
            <div class="cols3">
                <span class="value-box value-box--name">{{ $userForDisplay->name }}</span>
            </div>
        </td>
    </tr>
    <tr>
        <th>日付</th>
        <td>
            <div class="cols3">
                <span class="value-box">
                    {{ $displayDate ? \Carbon\Carbon::parse($displayDate)->format('Y年') : '' }}
                </span>
                <span></span>
                <span class="value-box">
                    {{ $displayDate ? \Carbon\Carbon::parse($displayDate)->format('n月j日') : '' }}
                </span>
            </div>
        </td>
    </tr>
    <tr>
        <th>出勤・退勤</th>
        <td>
            <div class="cols3">
                <span class="value-box">
                    {{ $displayClockIn ? substr($displayClockIn, 0, 5) : '' }}
                </span>
                <span class="tilde">~</span>
                <span class="value-box">
                    {{ $displayClockOut ? substr($displayClockOut, 0, 5) : '' }}
                </span>
            </div>
        </td>
    </tr>
    @foreach ($breakTimesForForm as $i => $breakTime)
        <tr>
            <th>休憩{{ $i === 0 ? '' : $i + 1 }}</th>
            <td>
                <div class="cols3 break-row">
                    <span class="value-box">
                        {{ $breakTime?->break_in_at ? substr($breakTime->break_in_at, 0, 5) : '' }}
                    </span>
                    <span class="tilde">~</span>
                    <span class="value-box">
                        {{ $breakTime?->break_out_at ? substr($breakTime->break_out_at, 0, 5) : '' }}
                    </span>
                </div>
            </td>
        </tr>
    @endforeach
    <tr>
        <th>備考</th>
        <td>
            <div class="remarks-readonly">
                {{ $stampRequest->remarks ?? ($pendingRequest->remarks ?? '') }}
            </div>
        </td>
    </tr>
</table>
