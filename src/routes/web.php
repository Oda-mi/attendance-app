<?php

use Illuminate\Support\Facades\Route;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AdminAttendanceController;


/*
|--------------------------------------------------------------------------
| 一般ユーザー用ルート
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/email/verify', function () {
        return view ('auth.verify-email');
    })->name('verification.notice');


    Route::get('/attendance',
        [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::view('/attendance/before-work', 'attendance.states.before_work')
        ->name('status.before_work');
    Route::view('/attendance/working', 'attendance.states.working')
        ->name('status.working');
    Route::view('/attendance/on-break', 'attendance.states.on_break')
        ->name('status.on_break');
    Route::view('/attendance/after-work', 'attendance.states.after_work')
        ->name('status.after_work');

    Route::post('/attendance/start',
        [AttendanceController::class, 'startWork'])
        ->name('attendance.start');
    Route::post('/attendance/start_break',
        [AttendanceController::class, 'startBreak'])
        ->name('attendance.start_break');
    Route::post('/attendance/end_break',
        [AttendanceController::class, 'endBreak'])
        ->name('attendance.end_break');
    Route::post('/attendance/end',
        [AttendanceController::class, 'endWork'])
        ->name('attendance.end');


    Route::get('/attendance/list',
        [AttendanceController::class, 'attendanceList'])
        ->name('attendance.list');


    Route::get('/attendance/detail/{id?}',
        [AttendanceController::class, 'attendanceDetail'])
        ->whereNumber('id')
        ->name('attendance.detail');

    Route::post('/attendance/detail/{id?}',
        [AttendanceController::class, 'storeUpdateRequest'])
        ->whereNumber('id')
        ->name('attendance.update_request');

});


/*
|--------------------------------------------------------------------------
| 管理者用ルート
|--------------------------------------------------------------------------
*/

Route::get('/admin/login', function () {
    return view('admin.auth.admin_login');
})->name('admin.login');

Route::post('/admin/login',
    [AuthenticatedSessionController::class, 'store'])
    ->name('admin.login');


Route::middleware(['auth'])->group(function () {

    Route::get('/admin/attendance/list',
        [AdminAttendanceController::class, 'dailyList'])
        ->name('admin.attendance.daily_list');


    Route::get('/admin/attendance/{id}',
        [AdminAttendanceController::class, 'adminAttendanceDetail'])
        ->whereNumber('id')
        ->name('admin.attendance.detail');

    Route::post('/admin/attendance/{id?}',
        [AdminAttendanceController::class, 'upsertAttendance'])
        ->whereNumber('id')
        ->name('admin.attendance.upsert');


    Route::get('/admin/staff/list',
        [AdminAttendanceController::class, 'staffList'])
        ->name('admin.staff.list');

    Route::get('/admin/attendance/staff/{id}',
        [AdminAttendanceController::class, 'staffMonthlyList'])
        ->whereNumber('id')
        ->name('admin.attendance.monthly_list');

    Route::post('/admin/export',
        [AdminAttendanceController::class, 'export'])
        ->name('admin.attendance.export');

    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}',
        [StampCorrectionRequestController::class, 'showApproveForm'])
        ->whereNumber('attendance_correct_request_id')
        ->name('stamp_correction_request.showApproveForm');

    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}',
        [StampCorrectionRequestController::class, 'approve'])
        ->whereNumber('attendance_correct_request_id')
        ->name('stamp_correction_request.approve');

});

/*
|--------------------------------------------------------------------------
| 一般・管理 共通ルート
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/stamp_correction_request/list',
        [StampCorrectionRequestController::class, 'requestList'])
        ->name('stamp_correction_request.list');

});