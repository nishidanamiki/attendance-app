@extends('layouts.app')

@section('title', '申請一覧 - COACHTECH勤怠管理アプリ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/stamp_correction_request/list.css') }}">
@endsection

@section('content')
    <div class="request-list">
        <h1 class="page-title">申請一覧</h1>
        <nav class="tab-menu">
            <a href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}"
                class="tab {{ $tab === 'pending' ? 'is-active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}"
                class="tab {{ $tab === 'approved' ? 'is-active' : '' }}">
                承認済み
            </a>
        </nav>
        <table class="request-table">
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日付</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
            @forelse ($stampRequests as $stampRequest)
                <tr>
                    <td>
                        @if ($stampRequest->status === 'pending')
                            承認待ち
                        @elseif ($stampRequest->status === 'approved')
                            承認済み
                        @else
                            {{ $stampRequest->status }}
                        @endif
                    </td>
                    <td>{{ $stampRequest->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($stampRequest->work_date)->format('Y/m/d') }}</td>
                    <td>
                        <div class="remarks-cell">
                            {{ $stampRequest->remarks }}
                        </div>
                    </td>
                    <td>{{ $stampRequest->created_at->format('Y/m/d') }}</td>
                    <td>
                        @if ($stampRequest->attendance_id)
                            <a href="{{ route('stamp_correction_request.show', ['id' => $stampRequest->id]) }}">
                                詳細
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        {{ $tab === 'pending' ? '承認待ちの申請はありません' : '承認済みの申請はありません' }}
                    </td>
                </tr>
            @endforelse
        </table>
    </div>
@endsection
