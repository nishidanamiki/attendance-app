<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\USer;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AdminStaffController extends Controller
{
    private function minutesToTimeString(?int $minutes): string
    {
        if ($minutes === null) {
            return '';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    public function index()
    {
        $staffs = User::where('is_admin', false)->orderBy('name')->get();
        return view('admin.staff.list', compact('staffs'));
    }

    public function monthly(Request $request, $id)
    {
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

        if ($request->boolean('csv')) {
            return response()->streamDownload(function () use ($attendances) {
                $handle = fopen('php://output', 'w');
                $header = ['日付', '出勤', '退勤', '休憩', '合計'];
                mb_convert_variables('SJIS-win', 'UTF-8', $header);
                fputcsv($handle, $header);
                foreach ($attendances as $attendance) {
                    $date = $attendance->work_date;
                    $clockIn = $attendance->clock_in_at ? substr($attendance->clock_in_at, 0, 5) : '';
                    $clockOut = $attendance->clock_out_at ? substr($attendance->clock_out_at, 0, 5) : '';
                    $totalBreakMinutes = 0;

                    foreach ($attendance->breakTimes as $break) {
                        if ($break->break_in_at && $break->break_out_at) {
                            $in = Carbon::createFromFormat('H:i:s', $break->break_in_at);
                            $out = Carbon::createFromFormat('H:i:s', $break->break_out_at);
                            $totalBreakMinutes += $in->diffInMinutes($out);
                        }
                    }

                    $totalWorkMinutes = null;
                    if ($attendance->clock_in_at && $attendance->clock_out_at) {
                        $in = Carbon::createFromFormat('H:i:s', $attendance->clock_in_at);
                        $out = Carbon::createFromFormat('H:i:s', $attendance->clock_out_at);
                        $totalWorkMinutes = $in->diffInMinutes($out) - $totalBreakMinutes;
                        if ($totalWorkMinutes < 0) $totalWorkMinutes = 0;
                    }

                    $row = [
                        $date,
                        $clockIn,
                        $clockOut,
                        $this->minutesToTimeString($totalBreakMinutes),
                        $this->minutesToTimeString($totalWorkMinutes),
                    ];
                    mb_convert_variables('SJIS-win', 'UTF-8', $row);
                    fputcsv($handle, $row);
                }

                fclose($handle);
            }, 'attendance.csv', [
                'Content-type' => 'text/csv',
            ]);
        }

        return view('admin/attendance.monthly', compact(
            'currentMonth',
            'days',
            'attendances',
            'isAdmin',
            'targetUser'
        ));
    }
}
