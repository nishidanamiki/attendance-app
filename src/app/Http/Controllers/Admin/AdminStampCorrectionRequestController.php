<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequestBreakTime;
use Illuminate\Support\Facades\DB;

class AdminStampCorrectionRequestController extends Controller
{
    public function show($attendance_correct_request_id)
    {
        $stampRequest = StampCorrectionRequest::with(['attendance.user', 'attendance.breakTimes', 'breakTimes'])->findOrFail($attendance_correct_request_id);

        $attendance = $stampRequest->attendance;
        $displayDate = $stampRequest->work_date ?? $attendance->work_date;

        if ($stampRequest->status === 'pending') {
            $displayClockIn = $stampRequest->clock_in_at ?? $attendance->clock_in_at;
            $displayClockOut = $stampRequest->clock_out_at ?? $attendance->clock_out_at;
            $breakTimesForForm = $stampRequest->breakTimes;
            $breakTimesForForm = $breakTimesForForm->values()->push(null);
            $pendingRequest = $stampRequest;
        } else {
            $displayClockIn = $attendance->clock_in_at;
            $displayClockOut = $attendance->clock_out_at;
            $breakTimesForForm = $attendance->breakTimes;
            $breakTimesForForm = $breakTimesForForm->values()->push(null);
            $pendingRequest = null;
        }

        return view('admin.stamp_correction_request.show', compact('attendance', 'displayDate', 'displayClockIn', 'displayClockOut', 'breakTimesForForm', 'pendingRequest', 'stampRequest'));
    }

    public function approve($attendance_correct_request_id)
    {
        $stampRequest = StampCorrectionRequest::with(['attendance', 'breakTimes'])->findOrFail($attendance_correct_request_id);

        if ($stampRequest->status === 'approved') {
            return back()->with('status', 'この申請はすでに承認済みです');
        }

        DB::transaction(function () use ($stampRequest) {
            $attendance = $stampRequest->attendance;

            if (!$attendance) {
                throw new \RuntimeException('対応する勤怠データが見つかりません');
            }

            if (!is_null($stampRequest->clock_in_at)) {
                $attendance->clock_in_at = $stampRequest->clock_in_at;
            }

            if (!is_null($stampRequest->clock_out_at)) {
                $attendance->clock_out_at = $stampRequest->clock_out_at;
            }

            $attendance->save();

            $attendance->breakTimes()->delete();

            foreach ($stampRequest->breakTimes as $breakRequest) {
                $attendance->breakTimes()->create([
                    'break_in_at' => $breakRequest->break_in_at,
                    'break_out_at' => $breakRequest->break_out_at,
                ]);
            }

            $stampRequest->status = 'approved';
            $stampRequest->approved_by = auth()->id();
            $stampRequest->approved_at = now();
            $stampRequest->save();
        });

        return redirect()->route('admin.stamp_correction_request.show', $stampRequest->id);
    }
}
