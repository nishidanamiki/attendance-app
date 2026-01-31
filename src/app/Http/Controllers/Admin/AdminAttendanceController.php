<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUpdateAttendanceRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $targetDate = $request->input('date', now()->toDateString());

        $date = Carbon::parse($targetDate);

        $prevDate = $date->copy()->subDay()->toDateString();
        $nextDate = $date->copy()->addDay()->toDateString();

        $attendances = Attendance::with(['user', 'breakTimes'])->whereDate('work_date', $targetDate)->orderBy('clock_in_at')->get()->map(function ($attendance) {
            $workMinutes = 0;
            if ($attendance->clock_in_at && $attendance->clock_out_at) {
                $workMinutes = Carbon::parse($attendance->clock_in_at)->diffInMinutes(Carbon::parse($attendance->clock_out_at));
            }

            $breakMinutes = $attendance->breakTimes->sum(function ($break) {
                if (! $break->break_in_at || ! $break->break_out_at) {
                    return 0;
                }

                return Carbon::parse($break->break_in_at)->diffInMinutes(Carbon::parse($break->break_out_at));
            });

            $netMinutes = max($workMinutes - $breakMinutes, 0);

            $attendance->work_minutes = $workMinutes;
            $attendance->break_minutes = $breakMinutes;
            $attendance->net_minutes = $netMinutes;

            return $attendance;
        });

        return view('admin.attendance.list', [
            'targetDate' => $targetDate,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'attendances' => $attendances,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes', 'stampCorrectionRequests.breakTimes'])->findOrFail($id);

        $date = $attendance->work_date;

        $pendingRequest = $attendance->stampCorrectionRequests()->where('status', 'pending')->latest()->first();

        if ($pendingRequest) {
            $displayClockIn = $pendingRequest->clock_in_at ?? $attendance->clock_in_at;
            $displayClockOut = $pendingRequest->clock_out_at ?? $attendance->clock_out_at;
            $breakTimesForForm = $pendingRequest->breakTimes->sortBy('break_in_at')->values();
        } else {
            $displayClockIn = $attendance->clock_in_at;
            $displayClockOut = $attendance->clock_out_at;
            $breakTimesForForm = $attendance->breakTimes->sortBy('break_in_at')->values();
            $breakTimesForForm->push(null);
        }

        $userForDisplay = $attendance->user;

        return view('admin.attendance.detail', compact('attendance', 'date', 'displayClockIn', 'displayClockOut', 'breakTimesForForm', 'pendingRequest', 'userForDisplay'));
    }

    public function update(AdminUpdateAttendanceRequest $request, $id)
    {
        $data = $request->validated();

        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        $attendance->clock_in_at = $data['clock_in_at'] ?? null;
        $attendance->clock_out_at = $data['clock_out_at'] ?? null;
        $attendance->remarks = $data['remarks'];
        $attendance->save();

        $breakInput = $data['breaks'] ?? [];

        foreach ($breakInput as $input) {

            $start = $input['start'] ?? null;
            $end = $input['end'] ?? null;

                if (!empty($input['id'])) {
                    $break = $attendance->breakTimes()->where('id', $input['id'])->first();
                    if ($break) {
                        if ($start === null && $end === null) {
                            $break->delete();
                        } else {
                            $break->break_in_at = $start;
                            $break->break_out_at = $end;
                            $break->save();
                        }
                    }
                    continue;
                }
                if ($start !== null && $end !== null) {
                    $attendance->breakTimes()->create([
                    'break_in_at' => $start,
                    'break_out_at' => $end,
                ]);
            }
        }
        return redirect()->route('admin.attendance.show', $attendance->id);
    }
}
