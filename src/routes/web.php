<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;
use Dom\Attr;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/admin/login', function () {
    return view('admin.auth.login');
})->name('admin.login');

Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock_in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock_out');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.break_in');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.break_out');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.list');
    Route::post('/stamp-correction-requests', [StampCorrectionRequestController::class, 'store'])->name('stamp_correction_request.store');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/attendance/detail', [AttendanceController::class, 'openByDate'])->name('attendance.openByDate');
});


Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff.list');
    Route::get('/requests/list', [AdminRequestController::class, 'index'])->name('stamp_correction_request.list');
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'monthly'])->name('attendance.monthly');
});

