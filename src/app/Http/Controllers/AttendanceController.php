<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
// use PhpParser\Node\Expr\FuncCall;

class AttendanceController extends Controller
{
    public function index()
    {
        $now = Carbon::now()->locale('ja');

        $attendance = Attendance::where('user_id', auth()->id())->whereDate('work_date', today())->first();

        if (!$attendance) {
            $status = 'OFF';
        } elseif ($attendance->clock_out_at) {
            $status = 'DONE';
        } else {
            $onBreak = $attendance->breakTimes()->whereNull('break_out_at')->exists();
            $status = $onBreak ? 'BREAK' : 'WORKING';
        }

        return view('attendance.index', compact('now', 'status'));
    }

    public function clockIn()
    {
        $userId = Auth::id();
        $today = today();

        Attendance::create([
            'user_id' => $userId,
            'work_date' => $today,
            'clock_in_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakIn()
    {
        $attendance = Attendance::where('user_id', auth()->id())->whereDate('work_date', today())->first();

        if (!$attendance || !$attendance->clock_in_at || $attendance->clock_out_at) {
            return redirect()->route('attendance.index');
        }

        $alreadyOnBreak = $attendance->breakTimes()->whereNull('break_out_at')->exists();

        if ($alreadyOnBreak) {
            return redirect()->route('attendance.index');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in_at' => now(),
            'break_out_at' => null,
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakOut()
    {
        $attendance = Attendance::where('user_id', auth()->id())->whereDate('work_date', today())->first();

        if (!$attendance || !$attendance->clock_in_at || $attendance->clock_out_at) {
            return redirect()->route('attendance.index');
        }

        $breakTime = $attendance->breakTimes()->whereNull('break_out_at')->first();

        if (!$breakTime) {
            return redirect()->route('attendance.index');
        }

        $breakTime->update([
            'break_out_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function clockOut()
    {
        $attendance = Attendance::where('user_id', auth()->id())->whereDate('work_date', today())->first();

        $onBreak = $attendance ? $attendance->breakTimes()->whereNull('break_out_at')->exists() : false;

        if (!$attendance || $attendance->clock_out_at || $onBreak) {
            return redirect()->route('attendance.index');
        }

        $attendance->update([
            'clock_out_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function list(Request $request)
    {
        $user = $request->user();

        $monthParam = $request->query('month');
        if ($monthParam) {
            $currentMonth = Carbon::createFormFormat('Y-m', $monthParam)->startOFMonth();
        } else {
            $currentMonth = Carbon::now()->startOfMonth();
        }

        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        $days = CarbonPeriod::create($start, $end);

        $attendances = Attendance::with('breakTimes')->where('user_id', $user->id)->whereBetween('work_date', [$start->toDateString(),$end->toDateString()])->get()->keyBy('work_date');

        $isAdmin = false;
        $targetUser = $user;

        return view('attendance.list', compact('attendances', 'currentMonth', 'days', 'isAdmin', 'targetUser'));
    }

    public function show($id)
    {
        $loginUser = auth()->user();

        $query = Attendance::with(['user', 'breakTimes'])->where('id', $id);

        if (! $loginUser->is_admin) {
            $query->where('user_id', $loginUser->id);
        }

        $attendance = $query->firstOrFail();

        $date = $attendance->work_date;

        $pendingRequest = null;

        if ($attendance) {
            $pendingRequest = StampCorrectionRequest::with('breakRequests')->where('attendance_id', $attendance->id)->where('status', 'pending')->first();
        }

        if ($pendingRequest) {
            $displayClockIn = $pendingRequest->clock_in_at ?? $attendance->clock_in_at;
            $displayClockOut = $pendingRequest->clock_out_at ?? $attendance->clock_out_at;

            $breakTimesForForm = $pendingRequest->breakRequests->sortBy('break_in_at')->values();
        } else {
            $displayClockIn = $attendance->clock_in_at;
            $displayClockOut = $attendance->clock_out_at;

            $breakTimes = $attendance->breakTimes->sortBy('break_in_at')->values();

            $breakTimesForForm = $breakTimes->push(null);
        }

        return view('attendance.detail', compact('attendance', 'date', 'displayClockIn', 'displayClockOut', 'breakTimesForForm', 'pendingRequest'));
    }

    public function openByDate(Request $request)
    {
        $date = $request->query('date');

        $attendance = Attendance::with('breakTimes')->where('user_id', auth()->id())->whereDate('work_date', $date)->first();

        $pendingRequest = null;
        if ($attendance) {
            $pendingRequest = StampCorrectionRequest::with('breakRequests')->where('attendance_id', $attendance->id)->where('status', 'pending')->first();
        }

        if ($pendingRequest) {
            $displayClockIn = $pendingRequest->clock_in_at ?? $attendance?->clock_in_at;
            $displayClockOut = $pendingRequest->clock_out_at ?? $attendance?->clock_out_at;

            $breakTimesForForm = $pendingRequest->breakRequests->sortBy('break_in_at')->values();
        } else {
            $displayClockIn = $attendance?->clock_in_at;
            $displayClockOut = $attendance?->clock_out_at;

            $breakTimes = $attendance ? $attendance->breakTimes->sortBy('break_in_at')->values() : collect();

            $breakTimesForForm = $breakTimes->push(null);
        }

        return view('attendance.detail', compact('attendance', 'date', 'displayClockIn', 'displayClockOut', 'breakTimesForForm', 'pendingRequest'));
    }
}
