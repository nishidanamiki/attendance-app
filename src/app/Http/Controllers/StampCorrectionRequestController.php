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
        $tab = $request->query('tab', 'pending');

        $query = StampCorrectionRequest::where('user_id', auth()->id());

        if ($tab === 'approved') {
            $query->where('status', 'APPROVE');
        } else {
            $tab = 'pending';
            $query->where('status', 'PENDING');
        }

        $requests = $query->orderByDesc('created_at')->get();

        return view('stamp_correction_request.list', compact('requests', 'tab'));
    }

    public function store(StoreStampCorrectionRequest $request)
    {
        $validated = $request->validated();

        $attendanceId = $validated['attendance_id'] ?? null;
        $workDate = $validated['work_date'] ?? null;

        if (!$attendanceId) {
            $attendance = Attendance::firstOrCreate(
                [
                    'user_id' => auth()->id(),
                    'work_date' => $workDate,
                ],
            );

            $attendanceId = $attendance->id;
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

            $requestModel->breakRequests()->create([
                'break_time_id' => $break['id'] ?? null,
                'break_in_at' => $start,
                'break_out_at' => $end,
            ]);
        }

        if ($attendanceId) {
            return redirect()->route('attendance.show', $attendanceId);
        }

        return redirect()->route('attendance.openByDate', ['date' => $workDate]);
    }
}
