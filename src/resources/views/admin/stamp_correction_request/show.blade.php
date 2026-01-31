@extends('layouts.app')

@section('title', '管理者・修正承認画面 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/show.css') }}">
@endsection

@section('content')
    <div class="detail-container">
        <h1 class="page-title">勤怠詳細</h1>
        @include('attendance.partials.detail_table_text')
        @if ($stampRequest->status === 'pending')
            <form action="{{ route('admin.stamp_correction_request.approve', $stampRequest->id) }}" method="POST">
                @csrf
                @method('patch')
                <button class="approve-button" type="submit">承認</button>
            </form>
        @elseif ($stampRequest->status === 'approved')
            <p class="approved-label">承認済み</p>
        @endif
    </div>
@endsection
