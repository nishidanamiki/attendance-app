@extends('layouts.app')

@section('title', '詳細画面 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
    @php
        $displayDate = $attendance?->work_date ?? ($date ?? null);
        $user = $attendance?->user ?? auth()->user();
    @endphp
    <div class="detail-container">
        <h1 class="page-title">勤怠詳細</h1>
        <form action="{{ route('stamp_correction_request.store') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="work_date" value="{{ $displayDate }}">
            <input type="hidden" name="attendance_id" value="{{ $attendance?->id ?? '' }}">
            <table class="detail-table">
                <tr>
                    <th>名前</th>
                    <td>
                        <div class="cols3">
                            <span class="value-box">{{ $user->name }}</span>
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
                        @if ($pendingRequest)
                            <div class="cols3">
                                <span class="value-box">
                                    {{ $displayClockIn ? substr($displayClockIn, 0, 5) : '' }}
                                </span>
                                <span class="tilde">~</span>
                                <span class="value-box">
                                    {{ $displayClockOut ? substr($displayClockOut, 0, 5) : '' }}
                                </span>
                            </div>
                        @else
                            <div class="cols3">
                                <input type="time" name="clock_in_at"
                                    value="{{ $displayClockIn ? substr($displayClockIn, 0, 5) : '' }}">
                                <span class="tilde">~</span>
                                <input type="time" name="clock_out_at"
                                    value="{{ $displayClockOut ? substr($displayClockOut, 0, 5) : '' }}">
                            </div>
                            <div class="form__error">
                                @error('clock_in_at')
                                    {{ $message }}
                                @enderror
                            </div>
                        @endif
                    </td>
                </tr>
                @foreach ($breakTimesForForm as $i => $breakTime)
                    <tr>
                        <th>
                            {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                        </th>
                        <td>
                            <div class="cols3 break-row">
                                @if ($pendingRequest)
                                    <span class="value-box">
                                        {{ $breakTime?->break_in_at ? substr($breakTime->break_in_at, 0, 5) : '' }}
                                    </span>
                                    <span class="tilde">~</span>
                                    <span class="value-box">
                                        {{ $breakTime?->break_out_at ? substr($breakTime->break_out_at, 0, 5) : '' }}
                                    </span>
                                @else
                                    <input type="hidden" name="breaks[{{ $i }}][id]"
                                        value="{{ $breakTime?->id ?? '' }}">
                                    <input type="time" name="breaks[{{ $i }}][start]"
                                        value="{{ $breakTime?->break_in_at ? substr($breakTime->break_in_at, 0, 5) : '' }}">
                                    <span class="tilde">~</span>
                                    <input type="time" name="breaks[{{ $i }}][end]"
                                        value="{{ $breakTime?->break_out_at ? substr($breakTime->break_out_at, 0, 5) : '' }}">
                                @endif
                            </div>
                            @if (!$pendingRequest)
                                @php
                                    $breakError = $errors->first("breaks.$i.start") ?: $errors->first("breaks.$i.end");
                                @endphp
                                @if ($breakError)
                                    <div class="form__error">
                                        {{ $breakError }}
                                    </div>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <th>
                        <label for="remarks">備考</label>
                    </th>
                    <td>
                        @if ($pendingRequest)
                            <div class="remarks-readonly">
                                {{ $pendingRequest->remarks }}
                            </div>
                        @else
                            <div class="cols3">
                                <textarea class="remarks" name="remarks" id="remarks" rows="3">{{ old('remarks') }}</textarea>
                            </div>
                            <div class="form__error">
                                @error('remarks')
                                    {{ $message }}
                                @enderror
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
            @if (!$pendingRequest)
                <button class="submit" type="submit">修正</button>
            @endif
            @if ($pendingRequest)
                <p class="notice">*承認待ちのため修正はできません。</p>
            @endif
        </form>
    </div>
@endsection
