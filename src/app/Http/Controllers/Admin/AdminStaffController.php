<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\USer;
use Carbon\Carbon;

class AdminStaffController extends Controller
{
    public function index()
    {
        $staffs = User::where('is_admin', false)->orderBy('name')->get();
        return view('admin.staff.list', compact('staffs'));
    }
}
