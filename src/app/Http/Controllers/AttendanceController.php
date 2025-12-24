<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;

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
}
