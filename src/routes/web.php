<?php

use Illuminate\Support\Facades\Route;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AdminAttendanceController;




/*
|--------------------------------------------------------------------------
| 一般ユーザー（ログイン前）
|--------------------------------------------------------------------------
*/



/*
|--------------------------------------------------------------------------
| 一般ユーザー（ログイン後）
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/email/verify', function () {
        return view ('auth.verify-email');
    })->name('verification.notice');


    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::view('/attendance/before-work', 'attendance.states.attendance_before_work')->name('attendance.before_work');
    Route::view('/attendance/working', 'attendance.states.attendance_working')->name('attendance.working');
    Route::view('/attendance/on-break', 'attendance.states.attendance_on_break')->name('attendance.on_break');
    Route::view('/attendance/after-work', 'attendance.states.attendance_after_work')->name('attendance.after_work');

    Route::post('/attendance/start', [AttendanceController::class, 'startWork'])->name('attendance.start');
    Route::post('/attendance/start_break', [AttendanceController::class, 'startBreak'])->name('attendance.start_break');
    Route::post('/attendance/end_break', [AttendanceController::class, 'endBreak'])->name('attendance.end_break');
    Route::post('/attendance/end', [AttendanceController::class, 'endWork'])->name('attendance.end');

    Route::get('/attendance/list', [AttendanceController::class, 'attendanceList'])->name('attendance.list');

    Route::get('/attendance/detail/{id?}', [AttendanceController::class, 'attendanceDetail'])->whereNumber('id')->name('attendance.detail');

    Route::post('/attendance/update-request', [AttendanceController::class, 'storeUpdateRequest'])->name('attendance.update_request');

    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'requestList'])->name('stamp_correction_request.list');

});


/*
|--------------------------------------------------------------------------
| 管理者
|--------------------------------------------------------------------------
*/

// 管理者ログイン
Route::get('/admin/login', function () {
    return view('admin.auth.admin_login');
})->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login');



Route::middleware(['auth'])->group(function () {

    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'dailyList'])->name('admin.attendance.list');

    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'adminAttendanceDetail'])->whereNumber('id')->name('admin.attendance.detail');

    Route::post('/admin/attendance/upsert', [AdminAttendanceController::class, 'upsertAttendance'])->name('admin.attendance.upsert');

    Route::get('/admin/staff/list', [AdminAttendanceController::class, 'staffList'])->name('admin.staff.list');

    Route::get('/admin/staff/{id}', [AdminAttendanceController::class, 'staffMonthlyList'])->whereNumber('id')->name('admin.attendance.staff');

    Route::post('/admin/export', [AdminAttendanceController::class, 'export'])->name('admin.attendance.export');

    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}',
        [StampCorrectionRequestController::class, 'showApproveForm'])
        ->whereNumber('attendance_correct_request_id')
        ->name('stamp_correction_request.showApproveForm');

    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}',
        [StampCorrectionRequestController::class, 'approve'])
        ->whereNumber('attendance_correct_request_id')
        ->name('stamp_correction_request.approve');

});



