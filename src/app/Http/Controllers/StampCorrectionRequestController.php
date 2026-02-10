<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use App\Http\Requests\StoreStampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $tab = $request->query('tab', 'pending');

        if ($tab === 'approved') {
            $status = 'approved';
        } else {
            $tab = 'pending';
            $status = 'pending';
        }

        if ($user->is_admin) {
            $requests = StampCorrectionRequest::with(['user', 'attendance'])->where('status', $status)->orderByDesc('created_at')->get();

            return view('admin.stamp_correction_request.list', compact('requests', 'tab'));
        }

        $requests = $user->stampCorrectionRequests()->where('status', $status)->orderByDesc('created_at')->get();

        return view('stamp_correction_request.list', compact('requests', 'tab'));
    }

    public function store(StoreStampCorrectionRequest $request)
    {
        $validated = $request->validated();

        if (auth()->user()->is_admin) abort(403);

        $attendanceId = $validated['attendance_id'] ?? null;
        $workDate = $validated['work_date'] ?? null;

        if ($attendanceId) {
            Attendance::where('id', $attendanceId)->where('user_id', auth()->id())->firstOrFail();
        }

        if (!$attendanceId) {
            $attendance = Attendance::firstOrCreate([
                'user_id' => auth()->id(),
                'work_date' => $workDate,
            ]);
            $attendanceId = $attendance->id;
        }

        $alreadyPending = StampCorrectionRequest::where('attendance_id', $attendanceId)->where('status', 'pending')->exists();

        if ($alreadyPending) {
            return back()->withErrors(['request' => 'この日の修正申請はすでに承認待ちです']);
        }

        $requestModel = StampCorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id'=> $attendanceId,
            'work_date' => $workDate,
            'clock_in_at' => $validated['clock_in_at'] ?? null,
            'clock_out_at' => $validated['clock_out_at'] ?? null,
            'remarks' => $validated['remarks'],
            'status' => 'pending',
        ]);

        foreach ($validated['breaks'] ?? [] as $break) {
            $start = $break['start'] ?? null;
            $end = $break['end'] ?? null;

            if (!$start && !$end) {
                continue;
            }

            $requestModel->breakTimes()->create([
                'break_time_id' => $break['id'] ?? null,
                'break_in_at' => $start,
                'break_out_at' => $end,
            ]);
        }
        return redirect()->route('attendance.detail', $attendanceId);
    }

    public function show($id)
    {
        $user = auth()->user();

        $stampRequest = StampCorrectionRequest::with(['attendance.user', 'attendance.breakTimes', 'breakTimes'])->where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $attendance = $stampRequest->attendance;

        $displayDate = $stampRequest->work_date ?? $attendance?->work_date;

        $displayClockIn = $stampRequest->clock_in_at ?? $attendance?->clock_in_at;
        $displayClockOut = $stampRequest->clock_out_at ?? $attendance?->clock_out_at;
        $breakTimesForForm = $stampRequest->breakTimes;

        $pendingRequest = $stampRequest;

        return view('stamp_correction_request.show', compact(
            'stampRequest',
            'attendance',
            'displayDate',
            'displayClockIn',
            'displayClockOut',
            'breakTimesForForm',
            'pendingRequest'
        ));
    }
}
