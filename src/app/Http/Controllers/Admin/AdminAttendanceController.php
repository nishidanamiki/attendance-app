<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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

    public function monthly(Request $request, $id) {
        $targetUser = User::findOrFail($id);

        $monthParam = $request->query('month');
        if ($monthParam) {
            $currentMonth = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } else {
            $currentMonth = Carbon::now()->startOfMonth();
        }

        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        $days = CarbonPeriod::create($start, $end);

        $attendances = Attendance::with('breakTimes')->where('user_id', $targetUser->id)->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])->get()->keyBy('work_date');

        $isAdmin = true;

        return view('attendance.list', compact(
            'currentMonth',
            'days',
            'attendances',
            'isAdmin',
            'targetUser'
        ));
    }
}
