@php
    $displayDate = $displayDate ?? ($date ?? ($attendance->work_date ?? null));
    $userForDisplay = $userForDisplay ?? ($attendance->user ?? (auth()->user()->is_admin ? null : auth()->user()));
    $clockInValue = old('clock_in_at', $displayClockIn ? substr($displayClockIn, 0, 5) : '');
    $clockOutValue = old('clock_out_at', $displayClockOut ? substr($displayClockOut, 0, 5) : '');
@endphp

<table class="detail-table">
    <tr>
        <th>名前</th>
        <td>
            <div class="cols3">
                <span class="value-box value-box--name">{{ $userForDisplay->name ?? '(ユーザー不明)' }}</span>
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
        <th><label for="clock_in_at">出勤・退勤</label></th>
        <td>
            <div class="cols3">
                <input id="clock_in_at" type="time" name="clock_in_at" value="{{ $clockInValue }}">
                <span class="tilde">~</span>
                <input id="clock_out_at" type="time" name="clock_out_at" value="{{ $clockOutValue }}">
            </div>
            <div class="form__error">
                @error('clock_in_at')
                    {{ $message }}
                @enderror
            </div>
            <div class="form__error">
                @error('clock_out_at')
                    {{ $message }}
                @enderror
            </div>
        </td>
    </tr>
    @foreach ($breakTimesForForm as $i => $breakTime)
        @php
            $breakIdValue = old("breaks.$i.id", $breakTime?->id ?? '');
            $breakStartValue = old(
                "breaks.$i.start",
                $breakTime?->break_in_at ? substr($breakTime->break_in_at, 0, 5) : '',
            );
            $breakEndValue = old(
                "breaks.$i.end",
                $breakTime?->break_out_at ? substr($breakTime->break_out_at, 0, 5) : '',
            );
            $breakError = $errors->first("breaks.$i.start") ?: $errors->first("breaks.$i.end");
        @endphp

        <tr>
            <th>
                <label for="break_{{ $i }}_start">
                    休憩{{ $i === 0 ? '' : $i + 1 }}
                </label>
            </th>
            <td>
                <div class="cols3 break-row">
                    <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $breakIdValue }}">
                    <input id="break_{{ $i }}_start" type="time"
                        name="breaks[{{ $i }}][start]" value="{{ $breakStartValue }}">
                    <span class="tilde">~</span>
                    <input type="time" name="breaks[{{ $i }}][end]" value="{{ $breakEndValue }}">
                </div>
                @if ($breakError)
                    <div class="form__error">
                        {{ $breakError }}
                    </div>
                @endif
            </td>
        </tr>
    @endforeach
    <tr>
        <th>
            <label for="remarks">備考</label>
        </th>
        <td>
            <div class="cols3">
                @php
                    $remarksValue = old('remarks', $pendingRequest->remarks ?? '');
                @endphp
                <textarea class="remarks" name="remarks" id="remarks" rows="3">{{ $remarksValue }}</textarea>
            </div>
            <div class="form__error">
                @error('remarks')
                    {{ $message }}
                @enderror
            </div>
        </td>
    </tr>
</table>
