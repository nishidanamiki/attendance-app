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

        return view('attendance.list', compact(
            'currentMonth',
            'days',
            'attendances',
            'isAdmin',
            'targetUser'
        ));
    }
}
