<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminStampCorrectionRequestController;
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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/email/verify', function() {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('attendance.index');
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/admin/login', function () {
    return view('admin.auth.login');
})->name('admin.login');

Route::middleware('auth', 'verified')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock_in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock_out');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.break_in');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.break_out');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.list');
    Route::post('/stamp_correction_requests', [StampCorrectionRequestController::class, 'store'])->name('stamp_correction_request.store');
    Route::get('/stamp_correction_request/{id}', [StampCorrectionRequestController::class, 'show'])->name('stamp_correction_request.show');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');
    Route::get('/attendance/detail', [AttendanceController::class, 'openByDate'])->name('attendance.openByDate');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
    Route::patch('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('attendance.update');
    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff.list');
    Route::get('/attendance/staff/{id}', [AdminStaffController::class, 'monthly'])->name('attendance.monthly');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminStampCorrectionRequestController::class, 'show'])->name('admin.stamp_correction_request.show');
    Route::patch('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminStampCorrectionRequestController::class, 'approve'])->name('admin.stamp_correction_request.approve');
});
