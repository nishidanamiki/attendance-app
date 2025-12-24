@extends('layouts.app')

@section('title', '勤怠登録 - COACHTECH勤怠アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
    <div class="attendance">
        <h1 class="visually-hidden">勤怠登録</h1>

        <div class="status">
            @if ($status === 'OFF')
                <p>勤務外</p>
            @elseif ($status === 'WORKING')
                <p>出勤中</p>
            @elseif ($status === 'BREAK')
                <p>休憩中</p>
            @elseif ($status === 'DONE')
                <p>退勤済</p>
            @endif
        </div>
        <div class="datetime">
            <div class="date" id="current-date"></div>
            <div class="time" id="current-time"></div>
        </div>
        <div class="buttons">
            @if ($status === 'OFF')
                <form action="{{ route('attendance.clock_in') }}" method="POST">
                    @csrf
                    <button class="button-submit" type="submit">出勤</button>
                </form>
            @elseif ($status === 'WORKING')
                <form action="{{ route('attendance.clock_out') }}" method="POST">
                    @csrf
                    <button class="button-submit button-submit__primary" type="submit">退勤</button>
                </form>
                <form action="{{ route('attendance.break_in') }}" method="POST">
                    @csrf
                    <button class="button-submit button-submit__secondary" type="submit">休憩入</button>
                </form>
            @elseif ($status === 'BREAK')
                <form action="{{ route('attendance.break_out') }}" method="POST">
                    @csrf
                    <button class="button-submit button-submit__secondary" type="submit">休憩戻</button>
                </form>
            @elseif ($status === 'DONE')
                <p class="attendance-message__done">お疲れ様でした。</p>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dateEl = document.getElementById('current-date');
            const timeEl = document.getElementById('current-time');

            function updateTime() {
                const now = new Date();

                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                timeEl.textContent = `${hours}:${minutes}`;

                const days = ['日', '月', '火', '水', '木', '金', '土'];
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const date = String(now.getDate()).padStart(2, '0');
                const day = days[now.getDay()];
                dateEl.textContent = `${year}年${month}月${date}日(${day})`;
            }

            updateTime();

            const now = new Date();
            const delay = (60 - now.getSeconds()) * 1000;

            setTimeout(() => {
                updateTime();
                setInterval(updateTime, 60000);
            }, delay);
        });
    </script>
@endsection
